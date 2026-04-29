<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Pdf;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\DocumentTokens;
use Lpdf\Engine\RenderOptions;
use PHPUnit\Framework\TestCase;

final class SnapshotTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function fixtureProvider(): array
    {
        return SnapshotHelper::fixtureProvider();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fixtureProvider')]
    public function testFixtureMatchesStoredHash(string $name): void
    {
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
        $xml    = file_get_contents(SnapshotHelper::fixtures() . "/$name.xml");
        $bytes  = Pdf::engine()->setLicenseKey('test-key')->render($xml);
        SnapshotHelper::compareOrUpdate($name, $bytes);
    }

    public function testOutputIsPdf(): void
    {
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
        $xml   = file_get_contents(SnapshotHelper::fixtures() . '/example1.xml');
        $bytes = Pdf::engine()->setLicenseKey('test-key')->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testCustomFontDoesNotThrow(): void
    {
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
        $xml    = file_get_contents(SnapshotHelper::fixtures() . '/example1.xml');
        $engine = Pdf::engine()->setLicenseKey('test-key');

        // Load a placeholder font (empty bytes will be ignored by the core if
        // the document does not reference it; we just assert no exception).
        $engine->loadFont('TestFont', '');
        $bytes = $engine->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testKitToXmlReturnsXmlDeclaration(): void
    {
        $doc = Pdf::document(null, [Pdf::section(null, [])]);
        $xml = Pdf::toXml($doc);
        self::assertStringStartsWith('<?xml version="1.0"', $xml);
    }

    public function testKitToXmlContainsLpdfRoot(): void
    {
        $doc = Pdf::document(null, [Pdf::section(null, [])]);
        $xml = Pdf::toXml($doc);
        self::assertStringContainsString('<lpdf version="1">', $xml);
    }

    public function testKitToXmlBuiltinFontInAssets(): void
    {
        $doc = Pdf::document(
            new DocumentAttr(tokens: new DocumentTokens(fonts: ['heading' => ['builtin' => 'Helvetica-Bold']])),
            [],
        );
        $xml = Pdf::toXml($doc);
        self::assertStringContainsString('<assets>', $xml);
        self::assertStringNotContainsString('<fonts>', $xml, '<fonts> wrapper must not appear in flat structure');
        self::assertStringContainsString('<font ', $xml);
        self::assertStringContainsString('core="Helvetica-Bold"', $xml);
        // Font must NOT appear inside <tokens>
        $tokensStart = strpos($xml, '<tokens>');
        $tokensEnd   = strpos($xml, '</tokens>');
        $fontInTokens = strpos($xml, '<font ', $tokensStart ?: 0);
        self::assertTrue(
            $tokensStart === false || $fontInTokens === false || $fontInTokens > $tokensEnd,
            'Font was incorrectly placed inside <tokens>',
        );
    }

    public function testKitToXmlCustomFontUsesRefAlias(): void
    {
        $doc = Pdf::document(
            new DocumentAttr(tokens: new DocumentTokens(fonts: ['body' => ['src' => '/fonts/MyFont.ttf']])),
            [],
        );
        $xml = Pdf::toXml($doc);
        self::assertStringContainsString('ref="body"', $xml);
        self::assertStringContainsString('src=', $xml, 'src= path should appear in XML (preserved for adapter auto-loading)');
    }

    public function testKitToXmlProducedXmlRendersToValidPdf(): void
    {
        $doc = Pdf::document(null, [
            Pdf::section(null, [Pdf::layout(null, [Pdf::text(null, ['Hello from kitToXml'])])]),
        ]);
        $engine = Pdf::engine()->setLicenseKey('test-key');
        $xml    = Pdf::toXml($doc);
        $bytes  = $engine->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testSetEncryptionProducesEncryptedPdf(): void
    {
        $xml    = file_get_contents(SnapshotHelper::fixtures() . '/example1.xml');
        $engine = Pdf::engine()->setLicenseKey('test-key');
        $engine->setEncryption('', 's3cr3t');
        $bytes  = $engine->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
        // Encrypted PDFs contain the /Encrypt dictionary entry
        self::assertStringContainsString('/Encrypt', $bytes);
    }

    public function testLoadImageDoesNotThrowAndProducesValidPdf(): void
    {
        // A minimal 1×1 white grayscale PNG
        $png1x1 = hex2bin(
            '89504e470d0a1a0a0000000d49484452000000010000000108000000003a7e9b55' .
            '0000000a49444154789c6260000000020001e221bc330000000049454e44ae426082',
        );
        $xml    = file_get_contents(SnapshotHelper::fixtures() . '/example1.xml');
        $engine = Pdf::engine()->setLicenseKey('test-key');
        $engine->loadImage('testimg', $png1x1);
        $bytes  = $engine->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    // ── Data binding ──────────────────────────────────────────────────────────

    private static function minDoc(string $body): string
    {
        return '<lpdf version="1"><document><section><layout>' . $body . '</layout></section></document></lpdf>';
    }

    public function testDataValueSubstitutesScalar(): void
    {
        $xml   = self::minDoc('<text data-value="name">Fallback</text>');
        $bytes = Pdf::engine()->setLicenseKey('test-key')->render($xml, new RenderOptions(data: ['name' => 'Acme Inc']));
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testDataSourceExpandsArray(): void
    {
        $xml  = self::minDoc('<stack data-source="items" gap="xs"><text data-value="label">Item</text></stack>');
        $data = ['items' => [['label' => 'Alpha'], ['label' => 'Beta'], ['label' => 'Gamma']]];
        $bytes = Pdf::engine()->setLicenseKey('test-key')->render($xml, new RenderOptions(data: $data));
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testDataIfHidesNodeWhenFalse(): void
    {
        $xml   = self::minDoc('<text data-if="isPremium">Premium only</text><text>Always visible</text>');
        $bytes = Pdf::engine()->setLicenseKey('test-key')->render($xml, new RenderOptions(data: ['isPremium' => false]));
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testNoDataRendersWithFallbackContent(): void
    {
        $xml   = self::minDoc('<text data-value="name">Inline fallback</text>');
        $bytes = Pdf::engine()->setLicenseKey('test-key')->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
    }
}
