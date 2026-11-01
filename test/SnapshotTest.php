<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\L;
use Lpdf\Kit\Cipher;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\DocumentMeta;
use Lpdf\Kit\DocumentOutput;
use Lpdf\Layout\ColumnAttr;
use Lpdf\Kit\Permission;
use Lpdf\Kit\DocumentAssets;
use Lpdf\Kit\FontAttr;
use Lpdf\Kit\ImageAttr;
use Lpdf\Kit\ProfileAttr;
use Lpdf\Kit\SectionAttr;
use Lpdf\Engine\EngineException;
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
        // showcase-encryption asks for encryption in its output: its snapshot is the file unencrypted.
        $bytes  = L::engine()->setLicenseKey('test-key')->render($xml, new RenderOptions(skipEncryption: true));
        SnapshotHelper::compareOrUpdate($name, $bytes);
    }

    public function testOutputIsPdf(): void
    {
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
        $xml   = file_get_contents(SnapshotHelper::fixtures() . '/example1.xml');
        $bytes = L::engine()->setLicenseKey('test-key')->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testCustomFontDoesNotThrow(): void
    {
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
        $xml    = file_get_contents(SnapshotHelper::fixtures() . '/example1.xml');
        $engine = L::engine()->setLicenseKey('test-key');

        // Load a placeholder font (empty bytes will be ignored by the core if
        // the document does not reference it; we just assert no exception).
        $engine->loadFont('TestFont', '');
        $bytes = $engine->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testKitToXmlReturnsXmlDeclaration(): void
    {
        $doc = L::document(null, [L::section(null, [])]);
        $xml = L::toXml($doc);
        self::assertStringStartsWith('<?xml version="1.0"', $xml);
    }

    public function testKitToXmlContainsLpdfRoot(): void
    {
        $doc = L::document(null, [L::section(null, [])]);
        $xml = L::toXml($doc);
        self::assertStringContainsString('<lpdf version="1">', $xml);
    }

    public function testKitToXmlWritesTheAssetsUnderTheSchemaNames(): void
    {
        $doc = L::document(
            new DocumentAttr(assets: new DocumentAssets(
                fonts: [
                    new FontAttr(name: 'heading', core: 'Helvetica-Bold'),
                    new FontAttr(name: 'body', ref: 'body-font', src: '/fonts/MyFont.ttf'),
                ],
                images: [new ImageAttr(name: 'logo', src: 'logo.png')],
            )),
            [],
        );
        $xml = L::toXml($doc);
        self::assertStringContainsString('<font name="heading" core="Helvetica-Bold"/>', $xml);
        self::assertStringContainsString('<font name="body" ref="body-font" src="/fonts/MyFont.ttf"/>', $xml);
        self::assertStringContainsString('<image name="logo" src="logo.png"/>', $xml);
    }

    public function testKitToXmlProducedXmlRendersToValidPdf(): void
    {
        $doc = L::document(null, [
            L::section(null, [L::layout(null, [L::text(null, ['Hello from kitToXml'])])]),
        ]);
        $engine = L::engine()->setLicenseKey('test-key');
        $xml    = L::toXml($doc);
        $bytes  = $engine->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    /** A document whose output has `$attributes`: by default, one that asks for AES-256. */
    private static function encryptedXml(string $attributes = 'encrypt="aes-256"'): string
    {
        return '<lpdf version="1"><document><output ' . $attributes . '/>'
            . '<section><layout><text>Hello</text></layout></section></document></lpdf>';
    }

    public function testEncryptsADocumentWhoseOutputAsksForItWithThePasswordsOfTheRender(): void
    {
        $bytes = L::engine()->setLicenseKey('test-key')
            ->render(self::encryptedXml(), new RenderOptions(userPassword: 'open', ownerPassword: 's3cr3t'));
        self::assertStringStartsWith('%PDF-', $bytes);
        self::assertStringContainsString('/Encrypt', $bytes);
        self::assertStringContainsString('/V 5', $bytes);
        self::assertStringContainsString('/R 6', $bytes);
        self::assertStringContainsString('/CFM /AESV3', $bytes);
    }

    public function testTakesTheCipherFromTheOutput(): void
    {
        $bytes = L::engine()->setLicenseKey('test-key')
            ->render(self::encryptedXml('encrypt="rc4-128"'), new RenderOptions(ownerPassword: 's3cr3t'));
        self::assertStringContainsString('/V 2', $bytes);
        self::assertStringContainsString('/R 3', $bytes);
        self::assertStringNotContainsString('AESV3', $bytes);
    }

    public function testTakesThePermissionsFromAllow(): void
    {
        $bytes = L::engine()->setLicenseKey('test-key')
            ->render(self::encryptedXml('encrypt="rc4-128" allow="print"'), new RenderOptions(ownerPassword: 's3cr3t'));
        // Printing, at full quality too, and nothing else.
        self::assertStringContainsString('/P -1852', $bytes);
    }

    public function testEveryAes256RenderGetsAFreshKey(): void
    {
        $engine  = L::engine()->setLicenseKey('test-key');
        $options = new RenderOptions(ownerPassword: 's3cr3t');
        self::assertNotSame(
            $engine->render(self::encryptedXml(), $options),
            $engine->render(self::encryptedXml(), $options),
        );
    }

    public function testRc4RendersOfOneDocumentAreIdentical(): void
    {
        $engine  = L::engine()->setLicenseKey('test-key');
        $options = new RenderOptions(ownerPassword: 's3cr3t');
        $xml     = self::encryptedXml('encrypt="rc4-128"');
        self::assertSame($engine->render($xml, $options), $engine->render($xml, $options));
    }

    public function testSendsTheUserPasswordToTheEngine(): void
    {
        // RC4-128 gives the same bytes for the same input, and the user password is in its /O and /U entries, so a
        // render with one differs from a render without.
        $engine = L::engine()->setLicenseKey('test-key');
        $xml    = self::encryptedXml('encrypt="rc4-128"');
        self::assertNotSame(
            $engine->render($xml, new RenderOptions(ownerPassword: 's3cr3t')),
            $engine->render($xml, new RenderOptions(userPassword: 'open', ownerPassword: 's3cr3t')),
        );
    }

    public function testSkipEncryptionRendersADocumentThatAsksForEncryptionUnencrypted(): void
    {
        $bytes = L::engine()->setLicenseKey('test-key')
            ->render(self::encryptedXml(), new RenderOptions(skipEncryption: true));
        self::assertStringNotContainsString('/Encrypt', $bytes);
    }

    /** @return array<string, array{string, RenderOptions, string}> */
    public static function refusedRenderProvider(): array
    {
        $plain = '<lpdf version="1"><document><section><layout><text>Hello</text></layout></section></document></lpdf>';
        return [
            'no owner password'           => [self::encryptedXml(), new RenderOptions(), 'no owner password'],
            'passwords without encrypt'   => [
                $plain,
                new RenderOptions(ownerPassword: 's3cr3t'),
                'does not ask for encryption',
            ],
            'passwords and skip'          => [
                self::encryptedXml(),
                new RenderOptions(ownerPassword: 's3cr3t', skipEncryption: true),
                'not both',
            ],
            'an unknown cipher'           => [
                self::encryptedXml('encrypt="aes-128"'),
                new RenderOptions(ownerPassword: 's3cr3t'),
                'aes-128',
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('refusedRenderProvider')]
    public function testRefusesARenderAndSaysWhatToChange(string $xml, RenderOptions $options, string $expected): void
    {
        $this->expectException(EngineException::class);
        $this->expectExceptionMessage($expected);
        L::engine()->setLicenseKey('test-key')->render($xml, $options);
    }

    /**
     * A document with no text of its own that asks for PDF/A: the attribution line's name, in its embedded face, is
     * its one text.
     */
    private static function pdfaXml(string $level = '2b', string $body = '<column background="#e8f0fc" height="40pt"/>'): string
    {
        return '<lpdf version="1"><document><output pdfa="' . $level . '"/>'
            . '<section><layout>' . $body . '</layout></section></document></lpdf>';
    }

    public function testWritesPdfaWhenTheOutputAsksForItDatedByCreatedOn(): void
    {
        $bytes = L::engine()->setLicenseKey('test-key')
            ->render(self::pdfaXml('2u'), new RenderOptions(createdOn: '2026-10-31T09:30:00Z'));
        self::assertStringContainsString('<pdfaid:part>2</pdfaid:part>', $bytes);
        self::assertStringContainsString('<pdfaid:conformance>U</pdfaid:conformance>', $bytes);
        self::assertStringContainsString('/S /GTS_PDFA1', $bytes);
        self::assertStringContainsString('/ModDate (D:20261031093000Z)', $bytes);
    }

    public function testTakesPdfaFromTheOutputOfAKitDocument(): void
    {
        $doc = L::document(
            new DocumentAttr(output: new DocumentOutput(pdfa: '2b')),
            [L::section(null, [L::layout(null, [L::column(new ColumnAttr(background: '#e8f0fc', height: '40pt'), [])])])],
        );
        self::assertSame(['pdfa' => '2b'], json_decode(json_encode($doc), true)['attrs']['output']);
        $bytes = L::engine()->setLicenseKey('test-key')->render($doc, new RenderOptions(createdOn: '2026-10-31'));
        self::assertStringContainsString('<pdfaid:conformance>B</pdfaid:conformance>', $bytes);
    }

    public function testTakesPdfuaAndLangFromAKitDocumentAndTagsTheFile(): void
    {
        $doc = L::document(
            new DocumentAttr(meta: new DocumentMeta(title: 'Report', lang: 'en-US'), output: new DocumentOutput(pdfua: '1')),
            [L::section(null, [L::layout(null, [L::column(new ColumnAttr(background: '#e8f0fc', height: '40pt'), [])])])],
        );
        self::assertSame(['pdfua' => '1'], json_decode(json_encode($doc), true)['attrs']['output']);
        $bytes = L::engine()->setLicenseKey('test-key')->render($doc, new RenderOptions(createdOn: '2026-10-31'));
        self::assertStringContainsString('<pdfuaid:part>1</pdfuaid:part>', $bytes);
        self::assertStringContainsString('/Lang (en-US)', $bytes);
        self::assertStringContainsString('/StructTreeRoot', $bytes);
    }

    public function testRefusesPdfuaWithoutTheLanguageOfTheDocument(): void
    {
        $this->expectException(EngineException::class);
        $this->expectExceptionMessage('add lang to <meta>');
        L::engine()->setLicenseKey('test-key')->render(
            '<lpdf version="1"><document><meta title="Report"/><output pdfua="1"/><section><layout>'
            . '<column height="40pt"/></layout></section></document></lpdf>',
            new RenderOptions(createdOn: '2026-10-31'),
        );
    }

    public function testRefusesPdfaWithoutCreatedOn(): void
    {
        $this->expectException(EngineException::class);
        $this->expectExceptionMessage('created_on');
        L::engine()->setLicenseKey('test-key')->render(self::pdfaXml());
    }

    public function testRefusesPdfaTextInABuiltInFont(): void
    {
        $this->expectException(EngineException::class);
        $this->expectExceptionMessage('"Hello" is in Helvetica');
        L::engine()->setLicenseKey('test-key')
            ->render(self::pdfaXml('2b', '<text>Hello</text>'), new RenderOptions(createdOn: '2026-10-31'));
    }

    /**
     * The header of an ICC printer profile in CMYK, with a description and nothing else: enough for the engine to take
     * it as the press profile of a PDF/X document.
     */
    private static function pressProfile(string $description = 'Test press'): string
    {
        $desc = 'desc' . str_repeat("\0", 4) . pack('N', strlen($description) + 1) . $description . "\0" . str_repeat("\0", 12);
        $header = str_repeat("\0", 12) . 'prtrCMYKLab ' . str_repeat("\0", 12) . 'acsp' . str_repeat("\0", 88);
        $bytes = $header . pack('N', 1) . 'desc' . pack('N', 144) . pack('N', strlen($desc)) . $desc;
        return pack('N', strlen($bytes)) . substr($bytes, 4);
    }

    /** A titled document with no text of its own that asks for PDF/X-4 with the profile `press`. */
    private static function pdfxXml(string $profile = '<profile name="press"/>'): string
    {
        return '<lpdf version="1"><assets>' . $profile . '</assets>'
            . '<document bleed="3mm"><meta title="Flyer"/><output pdfx="4" profile="press"/>'
            . '<section><layout><column background="#e8f0fc" height="40pt"/></layout></section></document></lpdf>';
    }

    public function testWritesPdfxWhenTheOutputAsksForItWithTheProfileFromLoadProfile(): void
    {
        $bytes = L::engine()->setLicenseKey('test-key')->loadProfile('press', self::pressProfile('Coated press'))
            ->render(self::pdfxXml(), new RenderOptions(createdOn: '2026-10-31T09:30:00Z'));
        self::assertStringStartsWith('%PDF-1.6', $bytes);
        self::assertStringContainsString('/S /GTS_PDFX', $bytes);
        self::assertStringContainsString('/Info (Coated press)', $bytes);
        self::assertStringContainsString('/TrimBox', $bytes);
        self::assertStringContainsString('/DefaultRGB', $bytes);
        self::assertStringContainsString('<pdfxid:GTS_PDFXVersion>PDF/X-4</pdfxid:GTS_PDFXVersion>', $bytes);
    }

    public function testTakesPdfxProfileBleedAndTheProfilesOfTheAssetsFromAKitDocument(): void
    {
        $src = tempnam(sys_get_temp_dir(), 'lpdf-profile-');
        file_put_contents($src, self::pressProfile());
        try {
            $doc = L::document(
                new DocumentAttr(
                    assets: new DocumentAssets(profiles: [new ProfileAttr(name: 'press', src: $src)]),
                    meta: new DocumentMeta(title: 'Flyer'),
                    output: new DocumentOutput(pdfx: '4', profile: 'press'),
                    bleed: '3mm',
                ),
                [L::section(new SectionAttr(bleed: '5mm'), [L::layout(null, [L::column(new ColumnAttr(background: '#e8f0fc', height: '40pt'), [])])])],
            );
            $attrs = json_decode(json_encode($doc), true)['attrs'];
            self::assertSame(['pdfx' => '4', 'profile' => 'press'], $attrs['output']);
            self::assertSame('3mm', $attrs['bleed']);
            $options = new RenderOptions(createdOn: '2026-10-31');
            $engine = L::engine()->setLicenseKey('test-key');
            $bytes = $engine->render($doc, $options);
            self::assertStringContainsString('/S /GTS_PDFX', $bytes);
            self::assertStringContainsString('/BleedBox [-14.173', $bytes);
            self::assertSame($bytes, $engine->render(L::toXml($doc), $options));
        } finally {
            unlink($src);
        }
    }

    public function testAProfileWithNoBytesIsAnErrorThatSaysHowToLoadIt(): void
    {
        $this->expectException(EngineException::class);
        $this->expectExceptionMessage("loadProfile('press', bytes)");
        L::engine()->setLicenseKey('test-key')->render(self::pdfxXml(), new RenderOptions(createdOn: '2026-10-31'));
    }

    public function testAProfileWhoseSrcCannotBeReadIsAnErrorThatNamesIt(): void
    {
        $this->expectException(EngineException::class);
        $this->expectExceptionMessage("profile 'press' of <assets>: cannot read its src '/no/such/press.icc'");
        L::engine()->setLicenseKey('test-key')->render(
            self::pdfxXml('<profile name="press" src="/no/such/press.icc"/>'),
            new RenderOptions(createdOn: '2026-10-31'),
        );
    }

    public function testEncryptsADocumentBuiltWithTheKitAsItsOutputAsks(): void
    {
        $doc = L::document(
            new DocumentAttr(output: new DocumentOutput(
                encrypt: Cipher::Aes256,
                allow: Permission::Print . ' ' . Permission::FillForms,
            )),
            [L::section(null, [L::layout(null, [L::text(null, ['Hello PDF'])])])],
        );
        self::assertSame(
            ['encrypt' => 'aes-256', 'allow' => 'print fill-forms'],
            json_decode(json_encode($doc), true)['attrs']['output'],
        );
        $bytes = L::engine()->setLicenseKey('test-key')->render($doc, new RenderOptions(ownerPassword: 's3cr3t'));
        self::assertStringContainsString('/CFM /AESV3', $bytes);
    }

    public function testLoadImageDoesNotThrowAndProducesValidPdf(): void
    {
        // A minimal 1×1 white grayscale PNG
        $png1x1 = hex2bin(
            '89504e470d0a1a0a0000000d49484452000000010000000108000000003a7e9b55' .
            '0000000a49444154789c6260000000020001e221bc330000000049454e44ae426082',
        );
        $xml    = file_get_contents(SnapshotHelper::fixtures() . '/example1.xml');
        $engine = L::engine()->setLicenseKey('test-key');
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
        $bytes = L::engine()->setLicenseKey('test-key')->render($xml, new RenderOptions(data: ['name' => 'Acme Inc']));
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testDataSourceExpandsArray(): void
    {
        $xml  = self::minDoc('<column data-source="items" gap="xs"><text data-value="label">Item</text></column>');
        $data = ['items' => [['label' => 'Alpha'], ['label' => 'Beta'], ['label' => 'Gamma']]];
        $bytes = L::engine()->setLicenseKey('test-key')->render($xml, new RenderOptions(data: $data));
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testDataIfHidesNodeWhenFalse(): void
    {
        $xml   = self::minDoc('<text data-if="isPremium">Premium only</text><text>Always visible</text>');
        $bytes = L::engine()->setLicenseKey('test-key')->render($xml, new RenderOptions(data: ['isPremium' => false]));
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testNoDataRendersWithFallbackContent(): void
    {
        $xml   = self::minDoc('<text data-value="name">Inline fallback</text>');
        $bytes = L::engine()->setLicenseKey('test-key')->render($xml);
        self::assertStringStartsWith('%PDF-', $bytes);
    }
}
