<?php
declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\L;
use Lpdf\Canvas\CanvasTextAttr;
use Lpdf\Canvas\LayerAttr;
use Lpdf\Canvas\RectAttr;
use Lpdf\Kit\BuiltinFont;
use Lpdf\Kit\DocumentAssets;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\FontAttr;
use Lpdf\Kit\ImageAttr;
use Lpdf\Kit\Orientation;
use Lpdf\Layout\FieldType;
use Lpdf\Layout\ImgAttr;
use Lpdf\Layout\Pin;
use Lpdf\Layout\FieldAttr;
use Lpdf\Layout\LinkAttr;
use Lpdf\Layout\SpanAttr;
use Lpdf\Layout\StackAttr;
use Lpdf\Layout\TextAttr;
use PHPUnit\Framework\TestCase;

/** The builders write the schema's names, and what they build renders like the XML it converts to. */
final class SchemaTest extends TestCase
{
    // -- Attribute names -------------------------------------------------------------------------

    public function testTextAlignAndBoldUseTheSchemaNames(): void
    {
        $json = $this->json(L::text(new TextAttr(align: 'right', bold: 'true'), ['x']));

        self::assertEquals(['align' => 'right', 'bold' => 'true'], $json['attrs']);
    }

    public function testLinkAndSpanCarryHref(): void
    {
        $link = $this->json(L::link(new LinkAttr(href: 'https://lpdf.io')));
        $span = $this->json(L::span(new SpanAttr(href: 'https://lpdf.io'), ['x']));

        self::assertSame(['href' => 'https://lpdf.io'], $link['attrs']);
        self::assertSame(['href' => 'https://lpdf.io'], $span['attrs']);
    }

    public function testFieldCarriesItsTypeAndNameAsAttributes(): void
    {
        $json = $this->json(L::field(new FieldAttr(type: 'text', name: 'email', maxLen: '40', actionUrl: 'https://lpdf.io')));

        self::assertEquals(
            ['type' => 'text', 'name' => 'email', 'max-len' => '40', 'action-url' => 'https://lpdf.io'],
            $json['attrs'],
        );
    }

    public function testARequiredAttributeCannotBeLeftOut(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new LinkAttr();
    }

    public function testDocumentFontAndDebugAreWritten(): void
    {
        $json = $this->json(L::document(new DocumentAttr(font: 'Times-Roman', debug: 'true')));

        self::assertSame('Times-Roman', $json['attrs']['font']);
        self::assertSame('true', $json['attrs']['debug']);
    }

    // -- Rendering -------------------------------------------------------------------------------

    public function testABuiltDocumentRendersTheSameAsItsXml(): void
    {
        $built = L::document(new DocumentAttr(size: 'a4'), [
            L::section(null, [
                L::layout(null, [
                    L::stack(new StackAttr(gap: '12pt'), [
                        L::text(new TextAttr(align: 'right', bold: 'true'), ['Title']),
                        L::text(null, ['Body ', L::span(new SpanAttr(bold: 'true'), ['bold']), ' text']),
                        L::link(new LinkAttr(href: 'https://lpdf.io'), [L::text(null, ['link'])]),
                    ]),
                ]),
                L::canvas(null, [
                    L::layer(new LayerAttr(page: 'each'), [
                        L::rect(new RectAttr(x: '40pt', y: '40pt', w: '100pt', h: '60pt', fill: '#ff0000', radius: '6pt')),
                        L::textAt(
                            new CanvasTextAttr(x: '40pt', y: '120pt', fontSize: '10pt'),
                            ['Canvas ', L::span(new SpanAttr(color: '#0000ff'), ['text'])],
                        ),
                    ]),
                ]),
            ]),
        ]);

        self::assertSame($this->render($built), $this->render(L::toXml($built)));
    }

    public function testBoldTextIsTheBoldFaceOfTheFont(): void
    {
        $doc = static fn (string $attrs): string => '<lpdf version="1"><document><section><layout><text '
            . $attrs . '>Hello</text></layout></section></document></lpdf>';

        self::assertSame($this->render($doc('bold="true"')), $this->render($doc('font="Helvetica-Bold"')));
        self::assertNotSame($this->render($doc('bold="true"')), $this->render($doc('')));
    }

    // -- Assets ----------------------------------------------------------------------------------

    public function testAssetsDeclareFontsAndImagesWithTheSchemaNames(): void
    {
        $assets = new DocumentAssets(
            fonts: [new FontAttr(name: 'heading', core: BuiltinFont::TimesBold)],
            images: [new ImageAttr(name: 'logo', ref: 'company-logo', src: 'logo.png')],
        );
        $json = $this->json(L::document(new DocumentAttr(assets: $assets)));

        self::assertSame(
            [
                'fonts'  => [['name' => 'heading', 'core' => 'Times-Bold']],
                'images' => [['name' => 'logo', 'ref' => 'company-logo', 'src' => 'logo.png']],
            ],
            $json['attrs']['assets'],
        );
        self::assertSame($assets, L::assets($assets));
    }

    public function testAnAssetNeedsAName(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new FontAttr(core: 'Helvetica');
    }

    public function testAFontDeclaredInTheAssetsIsTheFontTheTextIsSetIn(): void
    {
        $built = $this->documentWithAssets(
            new DocumentAssets(fonts: [new FontAttr(name: 'heading', core: BuiltinFont::TimesBold)]),
            L::text(new TextAttr(font: 'heading'), ['Hello']),
        );

        self::assertStringContainsString('/BaseFont /Times-Bold', L::engine()->setLicenseKey('test-key')->render($built));
        self::assertSame($this->render($built), $this->render(L::toXml($built)));
    }

    public function testAnImageDeclaredInTheAssetsCanBeUsedAndRendersTheSameAsItsXml(): void
    {
        $built = $this->documentWithAssets(
            new DocumentAssets(images: [new ImageAttr(name: 'logo')]),
            L::img(new ImgAttr(name: 'logo', width: '40pt')),
        );
        $engine = L::engine()->setLicenseKey('test-key')->loadImage('logo', self::pixel());

        self::assertSame($this->normalised($engine->render($built)), $this->normalised($engine->render(L::toXml($built))));
    }

    public function testAnImageUsedButNotDeclaredInTheAssetsIsAnErrorThatNamesIt(): void
    {
        $built = $this->documentWithAssets(null, L::img(new ImgAttr(name: 'ghost')));
        $engine = L::engine()->setLicenseKey('test-key')->loadImage('ghost', self::pixel());

        $this->expectExceptionMessageMatches('/ghost/');
        $engine->render($built);
    }

    public function testAnImageDeclaredWithASrcIsReadFromThere(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'lpdf-logo');
        file_put_contents($file, self::pixel());
        try {
            $built = $this->documentWithAssets(
                new DocumentAssets(images: [new ImageAttr(name: 'logo', src: $file)]),
                L::img(new ImgAttr(name: 'logo', width: '40pt')),
            );
            self::assertStringStartsWith('%PDF-', L::engine()->setLicenseKey('test-key')->render($built));
        } finally {
            unlink($file);
        }
    }

    public function testTheConstantsAreTheSchemaValues(): void
    {
        self::assertSame('text', FieldType::Text);
        self::assertSame('top', Pin::Top);
        self::assertSame('landscape', Orientation::Landscape);
        self::assertSame('Times-Bold', BuiltinFont::TimesBold);
    }

    // -- Helpers ---------------------------------------------------------------------------------

    private static function pixel(): string
    {
        return (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    }

    private function documentWithAssets(?DocumentAssets $assets, object ...$nodes): object
    {
        return L::document(new DocumentAttr(assets: $assets), [L::section(null, [L::layout(null, array_values($nodes))])]);
    }

    private function normalised(string $pdf): string
    {
        return (string) preg_replace(['~/CreationDate[^\n]*~', '~/ID *\[[^\]]*\]~'], '', $pdf);
    }

    /** @return array<string,mixed> */
    private function json(object $node): array
    {
        return json_decode(json_encode($node, JSON_THROW_ON_ERROR), true);
    }

    /** The PDF an input renders to, with the parts that vary left out. */
    private function render(string|object $input): string
    {
        $pdf = L::engine()->setLicenseKey('test-key')->render($input);
        return (string) preg_replace(['~/CreationDate[^\n]*~', '~/ID *\[[^\]]*\]~'], '', $pdf);
    }
}
