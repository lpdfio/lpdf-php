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
    /** Bytes of entropy sent with a request that has a password: one 256-bit key's worth. */
    private const ENTROPY_BYTES = 32;

    /** @var array<string, string> Font name → raw TTF/OTF bytes */
    private array $fonts = [];

    /** @var array<string, string> Image name → raw image bytes (PNG/JPEG/WebP/…) */
    private array $images = [];

    /** @var array<string, string> `ref` or name of a `<file>` of `<attachments>` → the bytes the PDF carries */
    private array $files = [];

    /** @var array<string, string> `src` of a `<markdown-styles>` → the text of the file of rules it names */
    private array $markdownStyles = [];

    private string $licenseKey = '';

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
     * Ask the engine what a license key is: valid, expired, for another product, and which
     * license and key it is.
     *
     * The engine's own verdict, from the same code a render runs — so a `licensed` status means
     * PDFs come out without the attribution line here. A key the portal considers perfectly good
     * still reads `unknown_key` in a build that does not trust the key it was signed with, which
     * is the answer worth having.
     *
     * @param  string|null $key  The key to check, or null for the one set on this engine.
     * @return array{status: string, product?: string, tier?: string, expires?: string,
     *               license?: string, key?: int}
     *         `status` is always present; the rest only once the signature verified.
     */
    public function checkLicenseKey(?string $key = null): array
    {
        $runner = new WasmRunner(
            wasmBinary: $this->options->wasmBinary ?? self::defaultBinary(),
            wasmRunner: $this->options->wasmRunner ?? 'wasmtime',
            timeout:    $this->options->timeout    ?? 30,
        );

        return $runner->invoke([
            'method' => 'check_license',
            'key'    => $key ?? $this->licenseKey,
            'now'    => time(),
        ]);
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
     * Register the bytes of a file the document carries, for the `<file>` of its `<attachments>` whose `ref`, or
     * with no ref whose name, is `$name`. The PDF carries them as they are, e.g.:
     *   XML:  <file name="invoice.xml" mime="text/xml" relationship="data"/>
     *   PHP:  $engine->loadFile('invoice.xml', $invoiceXml);
     */
    public function loadFile(string $name, string $bytes): static
    {
        $this->files[$name] = $bytes;
        return $this;
    }

    /**
     * Register the text of a file of Markdown rules, the root of which is a `<markdown-styles>`, for the `src`
     * that names it in `<markdown-styles src="…">`.
     */
    public function loadMarkdownStyles(string $src, string $text): static
    {
        $this->markdownStyles[$src] = $text;
        return $this;
    }

    /**
     * Render an lpdf XML string or PdfDocument tree and return raw PDF bytes.
     *
     * @param  string|PdfDocument  $input    XML string or a PdfDocument.
     * @param  RenderOptions|null  $options  Per-call options (createdOn, data, the passwords, skipEncryption).
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

        // An XML document is parsed once, for the src of every file it names.
        $xml = $method === 'render_pdf' ? self::parseXml($inputStr) : null;

        // Auto-load fonts declared via src= that haven't been explicitly provided.
        $mergedFonts = $this->fonts;
        $fontSrcs = $method === 'render_pdf'
            ? self::xmlSrcs($xml, '//assets/font')
            : self::jsonAssetSrcs($inputStr, 'fonts');
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
            ? self::xmlSrcs($xml, '//assets/image')
            : self::jsonAssetSrcs($inputStr, 'images');
        foreach ($imageSrcs as $key => $path) {
            if (!array_key_exists($key, $mergedImages) && is_readable($path)) {
                $bytes = file_get_contents($path);
                if ($bytes !== false) {
                    $mergedImages[$key] = $bytes;
                }
            }
        }

        // The files the document carries: the ones loaded here, and the ones read from the src of a file that wasn't.
        // A src that can't be read is an error that names the file, rather than the engine's "no bytes".
        $mergedFiles = $this->files;
        $fileSrcs = $method === 'render_pdf'
            ? self::xmlSrcs($xml, '//attachments/file')
            : self::jsonFileSrcs($inputStr);
        foreach ($fileSrcs as $key => $path) {
            if (array_key_exists($key, $mergedFiles)) {
                continue;
            }
            $bytes = is_readable($path) ? file_get_contents($path) : false;
            if ($bytes === false) {
                throw new EngineException("file '{$key}' of <attachments>: cannot read its src '{$path}'");
            }
            $mergedFiles[$key] = $bytes;
        }

        // The files of Markdown rules that a document names with a src: the ones loaded here, and the ones read
        // from the path. A file that can't be read is left for the engine to report, naming the src.
        $mergedStyles = $this->markdownStyles;
        $styleSrcs = $method === 'render_pdf'
            ? self::xmlMarkdownStylesSrcs($xml)
            : self::jsonMarkdownStylesSrcs($inputStr);
        foreach ($styleSrcs as $src) {
            if (!array_key_exists($src, $mergedStyles) && is_readable($src)) {
                $text = file_get_contents($src);
                if ($text !== false) {
                    $mergedStyles[$src] = $text;
                }
            }
        }

        $payload = [
            'method' => $method,
            'key'    => $this->licenseKey,
            'input'  => $inputStr,
            // The engine is WebAssembly and has no clock of its own: without this it cannot
            // check the key's expiry, and an expired key renders as though it were current.
            'now'    => time(),
        ];

        if ($mergedFonts !== []) {
            $payload['fonts'] = array_map('base64_encode', $mergedFonts);
        }

        if ($mergedImages !== []) {
            $payload['images'] = array_map('base64_encode', $mergedImages);
        }

        if ($mergedFiles !== []) {
            $payload['files'] = array_map('base64_encode', $mergedFiles);
        }

        if ($mergedStyles !== []) {
            $payload['markdown_styles'] = $mergedStyles;
        }

        if ($options?->createdOn !== null) {
            $payload['created_on'] = $options->createdOn;
        }

        if ($options?->userPassword !== null) {
            $payload['user_password'] = $options->userPassword;
        }
        if ($options?->ownerPassword !== null) {
            $payload['owner_password'] = $options->ownerPassword;
        }
        // With a password, AES-256 needs random bytes, which the engine does not make: they come from PHP's
        // secure generator, 32 bytes for each render.
        if ($options?->userPassword !== null || $options?->ownerPassword !== null) {
            $payload['entropy'] = base64_encode(random_bytes(self::ENTROPY_BYTES));
        }
        if ($options?->skipEncryption === true) {
            $payload['skip_encryption'] = true;
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

    /**
     * An XML document read with an XML parser, as the engine reads it, for the `src` of the files it names: entities
     * decoded, and what is in a comment or CDATA left out. XML that doesn't parse is null and names no file here; the
     * engine says what is wrong with it.
     */
    private static function parseXml(string $xml): ?\DOMXPath
    {
        // DOMDocument::loadXML refuses an empty string with a ValueError; the engine says what is wrong with it.
        if (trim($xml) === '') {
            return null;
        }
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        // LIBXML_NONET: nothing the XML names is fetched from the network.
        $parsed = $document->loadXML($xml, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return $parsed === false ? null : new \DOMXPath($document);
    }

    /**
     * The `ref ?? name` → file path of each element that `$path` finds in a document: `//assets/font`,
     * `//assets/image` or `//attachments/file`.
     *
     * @return array<string,string>
     */
    private static function xmlSrcs(?\DOMXPath $xml, string $path): array
    {
        $srcs = [];
        foreach ($xml?->query($path) ?: [] as $element) {
            if (!$element instanceof \DOMElement) {
                continue;
            }
            $key = $element->getAttribute('ref') !== '' ? $element->getAttribute('ref') : $element->getAttribute('name');
            $src = $element->getAttribute('src');
            if ($key !== '' && $src !== '') {
                $srcs[$key] = $src;
            }
        }
        return $srcs;
    }

    /** @return array<string,string> `ref ?? name` → file path of the files a serialised tree carries in `attachments`. */
    private static function jsonFileSrcs(string $json): array
    {
        $srcs = [];
        $doc  = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        foreach ($doc['attrs']['attachments'] ?? [] as $file) {
            if (isset($file['src'])) {
                $srcs[$file['ref'] ?? $file['name']] = $file['src'];
            }
        }
        return $srcs;
    }

    /** @return string[] The `src` of the `<markdown-styles>` of each `<markdown>` of a document. */
    private static function xmlMarkdownStylesSrcs(?\DOMXPath $xml): array
    {
        $srcs = [];
        foreach ($xml?->query('//markdown/markdown-styles') ?: [] as $styles) {
            if ($styles instanceof \DOMElement && $styles->getAttribute('src') !== '') {
                $srcs[] = $styles->getAttribute('src');
            }
        }
        return $srcs;
    }

    /** @return string[] The `src` of each `markdown-styles` in the markdown of any section of a serialised tree. */
    private static function jsonMarkdownStylesSrcs(string $json): array
    {
        $srcs = [];
        $doc  = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        foreach ($doc['nodes'] ?? [] as $section) {
            foreach ($section['nodes'] ?? [] as $child) {
                if (($child['type'] ?? null) !== 'markdown') {
                    continue;
                }
                foreach ($child['nodes'] ?? [] as $part) {
                    if (($part['type'] ?? null) === 'markdown-styles' && isset($part['attrs']['src'])) {
                        $srcs[] = $part['attrs']['src'];
                    }
                }
            }
        }
        return $srcs;
    }

    /**
     * @param 'fonts'|'images' $kind
     * @return array<string,string> `ref ?? name` → file path of the fonts or images a serialised tree
     *                              declares with a `src` in its `assets`.
     */
    private static function jsonAssetSrcs(string $json, string $kind): array
    {
        $srcs = [];
        $doc  = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        foreach ($doc['attrs']['assets'][$kind] ?? [] as $asset) {
            if (isset($asset['src'])) {
                $srcs[$asset['ref'] ?? $asset['name']] = $asset['src'];
            }
        }
        return $srcs;
    }

    private static function defaultBinary(): string
    {
        return \dirname(__DIR__) . '/resources/lpdf-wasi.wasm';
    }
}
