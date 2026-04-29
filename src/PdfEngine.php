<?php

declare(strict_types=1);

namespace Lpdf;

use Lpdf\Kit\PdfDocument;
use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\RenderOptions;
use Lpdf\Engine\WasmRunner;

final class PdfEngine
{
    /** @var array<string, string> Font name → raw TTF/OTF bytes */
    private array $fonts = [];

    /** @var array<string, string> Image name → raw image bytes (PNG/JPEG/WebP/…) */
    private array $images = [];

    private string $licenseKey = '';

    /**
     * Optional RC4-128 encryption config.
     * @var array{user_password: string, owner_password: string, permissions: array<string, bool>}|null
     */
    private ?array $encrypt = null;

    public function __construct(
        private readonly EngineOptions $options = new EngineOptions(),
    ) {}

    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Set the license key and return $this for fluent chaining.
     * Pass an empty string to render in evaluation mode (produces a visible watermark).
     */
    public function setLicenseKey(string $key): static
    {
        $this->licenseKey = $key;
        return $this;
    }

    /**
     * Configure RC4-128 encryption for all subsequent render() calls.
     * Pass null to clear previously set encryption.
     *
     * @param array<string, bool> $permissions  Flags: print, modify, copy, annotate,
     *                                          fill_forms, accessibility, assemble, print_hq.
     *                                          Omitted flags default to true (allowed).
     */
    public function setEncryption(string $userPassword, string $ownerPassword, array $permissions = []): static
    {
        $this->encrypt = [
            'user_password'  => $userPassword,
            'owner_password' => $ownerPassword,
            'permissions'    => $permissions,
        ];
        return $this;
    }

    /**
     * Remove any previously configured encryption.
     */
    public function clearEncryption(): static
    {
        $this->encrypt = null;
        return $this;
    }

    /**
     * Register a custom font asset.
     *
     * The name must match the `ref` attribute of a `<font>` element in the
     * XML `<assets>` block, e.g.:
     *   XML:  <font name="heading" ref="inter-bold"/>
     *   PHP:  $engine->loadFont('inter-bold', file_get_contents('InterBold.ttf'));
     */
    public function loadFont(string $name, string $bytes): static
    {
        $this->fonts[$name] = $bytes;
        return $this;
    }

    /**
     * Register an image asset.
     *
     * The name must match the `name` attribute of an `<image>` element in the
     * XML `<assets>` block, e.g.:
     *   XML:  <image name="logo"/>
     *   PHP:  $engine->loadImage('logo', file_get_contents('logo.png'));
     */
    public function loadImage(string $name, string $bytes): static
    {
        $this->images[$name] = $bytes;
        return $this;
    }

    /**
     * Render an lpdf XML string or PdfDocument tree and return raw PDF bytes.
     *
     * @param  string|PdfDocument  $input    XML string or a PdfDocument.
     * @param  RenderOptions|null  $options  Per-call options (createdOn, data).
     * @throws EngineException On render or process error.
     */
    public function render(string|PdfDocument $input, ?RenderOptions $options = null): string
    {
        if ($input instanceof PdfDocument) {
            $method   = 'render_tree_pdf';
            $inputStr = json_encode($input, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } else {
            $method   = 'render_pdf';
            $inputStr = $input;
        }

        $runner = new WasmRunner(
            wasmBinary: $this->options->wasmBinary ?? self::defaultBinary(),
            wasmRunner: $this->options->wasmRunner ?? 'wasmtime',
            timeout:    $this->options->timeout    ?? 30,
        );

        // Auto-load fonts declared via src= that haven't been explicitly provided.
        $mergedFonts = $this->fonts;
        $fontSrcs = $method === 'render_pdf'
            ? self::xmlFontSrcs($inputStr)
            : self::jsonFontSrcs($inputStr);
        foreach ($fontSrcs as $key => $path) {
            if (!array_key_exists($key, $mergedFonts) && is_readable($path)) {
                $bytes = file_get_contents($path);
                if ($bytes !== false) {
                    $mergedFonts[$key] = $bytes;
                }
            }
        }

        // Auto-load images declared via src= that haven't been explicitly provided.
        $mergedImages = $this->images;
        $imageSrcs = $method === 'render_pdf'
            ? self::xmlImageSrcs($inputStr)
            : self::jsonImageSrcs($inputStr);
        foreach ($imageSrcs as $key => $path) {
            if (!array_key_exists($key, $mergedImages) && is_readable($path)) {
                $bytes = file_get_contents($path);
                if ($bytes !== false) {
                    $mergedImages[$key] = $bytes;
                }
            }
        }

        $payload = [
            'method' => $method,
            'key'    => $this->licenseKey,
            'input'  => $inputStr,
        ];

        if ($mergedFonts !== []) {
            $payload['fonts'] = array_map('base64_encode', $mergedFonts);
        }

        if ($mergedImages !== []) {
            $payload['images'] = array_map('base64_encode', $mergedImages);
        }

        if ($options?->createdOn !== null) {
            $payload['created_on'] = $options->createdOn;
        }

        if ($this->encrypt !== null) {
            $payload['encrypt'] = $this->encrypt;
        }

        if ($options?->data !== null && $method === 'render_pdf') {
            $payload['data'] = $options->data;
        }

        $response = $runner->invoke($payload);

        if (!isset($response['pdf'])) {
            throw new EngineException('Unexpected response from WASI process.');
        }

        $bytes = base64_decode($response['pdf'], strict: true);
        if ($bytes === false) {
            throw new EngineException('Failed to decode base64 PDF response.');
        }

        return $bytes;
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /** @return array<string,string> Font ref/name → file path from `<font name="…" src="…">` tags. */
    private static function xmlFontSrcs(string $xml): array
    {
        $srcs = [];
        preg_match_all('/<font\s[^>]*>/', $xml, $m);
        foreach ($m[0] as $tag) {
            if (preg_match('/\bname="([^"]*)"/', $tag, $nm) &&
                preg_match('/\bsrc="([^"]*)"/', $tag, $src)) {
                $key = preg_match('/\bref="([^"]*)"/', $tag, $ref) ? $ref[1] : $nm[1];
                $srcs[$key] = $src[1];
            }
        }
        return $srcs;
    }

    /** @return array<string,string> Image ref/name → file path from `<image name="…" src="…">` tags. */
    private static function xmlImageSrcs(string $xml): array
    {
        $srcs = [];
        preg_match_all('/<image\s[^>]*>/', $xml, $m);
        foreach ($m[0] as $tag) {
            if (preg_match('/\bname="([^"]*)"/', $tag, $nm) &&
                preg_match('/\bsrc="([^"]*)"/', $tag, $src)) {
                $key = preg_match('/\bref="([^"]*)"/', $tag, $ref) ? $ref[1] : $nm[1];
                $srcs[$key] = $src[1];
            }
        }
        return $srcs;
    }

    /** @return array<string,string> Font ref/name → file path from a serialised tree's `tokens.fonts`. */
    private static function jsonFontSrcs(string $json): array
    {
        $srcs = [];
        $doc  = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        foreach ($doc['attrs']['tokens']['fonts'] ?? [] as $name => $def) {
            if (isset($def['src'])) {
                $key = $def['ref'] ?? $name;
                $srcs[$key] = $def['src'];
            }
        }
        return $srcs;
    }

    /** @return array<string,string> Image ref/name → file path from a serialised tree's `tokens.images`. */
    private static function jsonImageSrcs(string $json): array
    {
        $srcs = [];
        $doc  = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        foreach ($doc['attrs']['tokens']['images'] ?? [] as $name => $def) {
            if (isset($def['src'])) {
                $key = $def['ref'] ?? $name;
                $srcs[$key] = $def['src'];
            }
        }
        return $srcs;
    }

    private static function defaultBinary(): string
    {
        return \dirname(__DIR__) . '/resources/lpdf-wasi.wasm';
    }
}
