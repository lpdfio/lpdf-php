<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\EngineOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Every fixture that has a stored hash in `test/snapshots`, rendered through each runner: the native library and
 * `wasmtime` give the same bytes, the stored ones.
 *
 * Each request is the one core's snapshot test renders, with the same fonts, images, profile, files, date, passwords
 * and entropy (`src/core/src/snapshot_tests.rs` in core), and goes to the runner as `PdfEngine` sends one: core fixes
 * the encryption's entropy, which `PdfEngine` draws at random for each render. Left out are the snapshots that core's
 * tests build in code, with no fixture of their name: the Factur-X cases, the stamps, `layers_comprehensive` and
 * `showcase-markdown-presets`.
 */
final class RunnerHashTest extends TestCase
{
    /** Core's date for the standards, PDF/A, PDF/UA, PDF/X and Factur-X, which need one. */
    private const CREATED_ON = '2026-10-31T09:30:00Z';

    private const FONTS = [
        'body' => 'CrimsonText-Regular.ttf', 'italic' => 'CrimsonText-Italic.ttf',
        'heading' => 'CrimsonText-SemiBold.ttf', 'sans' => 'Montserrat-Regular.ttf',
    ];

    private const IMAGES = [
        'photo' => 'chapter-05-caterpillar.jpg', 'chart' => 'image-sample.png', 'badge' => 'image-square.png',
        'avatar' => 'image-square.png', 'tall' => 'image-tall.png',
    ];

    /**
     * What a fixture's render loads beside its XML, where it loads more: fonts and images by name, a press profile,
     * files from their `src`, the data beside it, a date, and passwords. Every other fixture renders alone.
     */
    private const RECIPES = [
        'showcase-columns-insets'   => ['images' => ['photo' => 'image-sample.png', 'avatar', 'tall']],
        'showcase-image-box'        => ['images' => ['photo' => 'image-sample.png', 'avatar', 'tall']],
        'showcase-image-fill'       => ['images' => ['photo' => 'image-sample.png', 'avatar', 'tall']],
        'showcase-markdown'         => ['images' => ['photo' => 'image-sample.png', 'avatar', 'tall']],
        'showcase-page-clip'        => ['images' => ['photo' => 'image-sample.png', 'avatar', 'tall']],
        'showcase-row-split-image'  => ['images' => ['photo' => 'image-sample.png', 'avatar', 'tall']],
        'showcase-bulk'             => ['data' => true],
        'showcase-columns-data'     => ['data' => true],
        'showcase-encryption'       => ['skipEncryption' => true],
        'showcase-encryption-rc4'   => ['fixture' => 'showcase-encryption', 'cipher' => 'rc4-128', 'passwords' => true],
        'showcase-encryption-aes256' => ['fixture' => 'showcase-encryption', 'passwords' => true],
        'showcase-pdfa-2a'          => ['fonts' => ['body', 'italic', 'heading', 'sans'], 'images' => ['photo', 'chart', 'badge'], 'date' => true],
        'showcase-pdfa-2b'          => ['fonts' => ['body', 'italic', 'heading', 'sans'], 'images' => ['photo', 'chart', 'badge'], 'date' => true],
        'showcase-pdfa-2u'          => ['fonts' => ['body', 'italic', 'heading', 'sans'], 'images' => ['photo', 'chart', 'badge'], 'date' => true],
        'showcase-pdfua'            => ['fonts' => ['body', 'heading', 'sans'], 'images' => ['chart', 'badge'], 'date' => true],
        'showcase-pdfua-toc'        => ['fonts' => ['body', 'heading', 'sans'], 'images' => ['chart', 'badge'], 'date' => true],
        'showcase-pdfx'             => ['fonts' => ['body', 'italic', 'heading', 'sans'], 'images' => ['photo', 'chart', 'badge'], 'profile' => true, 'date' => true],
        'showcase-linearized'       => ['fonts' => ['body', 'heading', 'sans'], 'images' => ['photo', 'chart', 'badge']],
        'showcase-linearized-pages' => ['fonts' => ['body', 'heading', 'sans'], 'images' => ['photo', 'chart', 'badge']],
        'showcase-linearized-encrypted' => ['fonts' => ['body', 'heading', 'sans'], 'images' => ['photo', 'chart', 'badge'], 'passwords' => true],
        'showcase-linearized-encrypted-rc4' => ['fixture' => 'showcase-linearized-encrypted', 'cipher' => 'rc4-128', 'fonts' => ['body', 'heading', 'sans'], 'images' => ['photo', 'chart', 'badge'], 'passwords' => true],
        'showcase-attachments'      => ['files' => true],
        'showcase-facturx'          => ['fonts' => ['body', 'heading', 'sans'], 'files' => true, 'date' => true],
    ];

    /** @return array<string, array{string, string}> Each runner and each snapshot with a fixture. */
    public static function cases(): array
    {
        if (!SnapshotHelper::hasFixtures()) {
            return ['no fixtures' => ['', '']];
        }
        $cases = [];
        foreach (RunnerSupport::runners() as [$runner]) {
            foreach (glob(SnapshotHelper::snapshots() . '/*.pdf.sha256') ?: [] as $snapshot) {
                $name = basename($snapshot, '.pdf.sha256');
                if (is_file(SnapshotHelper::fixtures() . '/' . self::fixtureOf($name) . '.xml')) {
                    $cases["$name through $runner"] = [$runner, $name];
                }
            }
        }
        return $cases;
    }

    #[DataProvider('cases')]
    public function testTheFixtureGivesItsStoredHash(string $runner, string $name): void
    {
        if ($name === '') {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
        RunnerSupport::need($runner);

        $answer = (new EngineOptions(runner: $runner, timeout: 0))->engineRunner()->invoke(self::request($name));

        $stored = trim((string) file_get_contents(SnapshotHelper::snapshots() . "/$name.pdf.sha256"));
        $this->assertSame($stored, hash('sha256', (string) base64_decode((string) $answer['pdf'], true)));
    }

    private static function fixtureOf(string $name): string
    {
        return self::RECIPES[$name]['fixture'] ?? $name;
    }

    /** @return array<string, mixed> The request of `$name`'s render, as core's snapshot test renders it. */
    private static function request(string $name): array
    {
        $recipe = self::RECIPES[$name] ?? [];
        $fixtures = SnapshotHelper::fixtures();
        $assets = \dirname($fixtures) . '/assets';
        $fixture = self::fixtureOf($name);
        $xml = (string) file_get_contents("$fixtures/$fixture.xml");
        if (isset($recipe['cipher'])) {
            $xml = str_replace('encrypt="aes-256"', "encrypt=\"{$recipe['cipher']}\"", $xml);
        }

        $request = ['method' => 'render_pdf', 'key' => '', 'input' => $xml];
        foreach ($recipe['fonts'] ?? [] as $font) {
            $request['fonts'][$font] = base64_encode((string) file_get_contents("$assets/fonts/" . self::FONTS[$font]));
        }
        foreach ($recipe['images'] ?? [] as $image => $file) {
            [$image, $file] = \is_int($image) ? [$file, self::IMAGES[$file]] : [$image, $file];
            $request['images'][$image] = base64_encode((string) file_get_contents("$assets/images/$file"));
        }
        if ($recipe['profile'] ?? false) {
            $request['profiles']['press'] = base64_encode((string) file_get_contents("$assets/profiles/FOGRA39L_coated.icc"));
        }
        if ($recipe['files'] ?? false) {
            $document = new \DOMDocument();
            $document->loadXML($xml);
            foreach ((new \DOMXPath($document))->query('//attachments/file[@src]') ?: [] as $file) {
                \assert($file instanceof \DOMElement);
                $key = $file->getAttribute('ref') !== '' ? $file->getAttribute('ref') : $file->getAttribute('name');
                $request['files'][$key] = base64_encode((string) file_get_contents("$fixtures/{$file->getAttribute('src')}"));
            }
        }
        if ($recipe['data'] ?? false) {
            // As objects, so an empty object stays one in the request.
            $request['data'] = json_decode((string) file_get_contents("$fixtures/$fixture.json"), false, 512, JSON_THROW_ON_ERROR);
        }
        if ($recipe['date'] ?? false) {
            $request['created_on'] = self::CREATED_ON;
        }
        if ($recipe['skipEncryption'] ?? false) {
            $request['skip_encryption'] = true;
        }
        // Core's fixed entropy, bytes 0 to 47, so the encrypted file is the same each time.
        if ($recipe['passwords'] ?? false) {
            $request += ['user_password' => 'user', 'owner_password' => 'owner', 'entropy' => base64_encode(implode('', array_map('chr', range(0, 47))))];
        }
        return $request;
    }
}
