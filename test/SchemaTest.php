<?php
declare(strict_types=1);

namespace Lpdf\Tests;

use const Lpdf\NoAttr;

use Lpdf\L;
use Lpdf\LMarkdown;
use Lpdf\Canvas\TextAtAttr;
use Lpdf\Canvas\LayerAttr;
use Lpdf\Canvas\RectAttr;
use Lpdf\Engine\EngineException;
use Lpdf\Engine\RenderOptions;
use Lpdf\Kit\BuiltinFont;
use Lpdf\Kit\DocumentAssets;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\DocumentTokens;
use Lpdf\Kit\FileAttr;
use Lpdf\Kit\FileRelationship;
use Lpdf\Kit\FontAttr;
use Lpdf\Kit\FootnoteCounter;
use Lpdf\Kit\ImageAttr;
use Lpdf\Kit\Orientation;
use Lpdf\Kit\SectionAttr;
use Lpdf\Kit\SectionType;
use Lpdf\Layout\BookmarkAttr;
use Lpdf\Layout\CodeAttr;
use Lpdf\Layout\CodeSpanAttr;
use Lpdf\Layout\FieldType;
use Lpdf\Layout\ListAttr;
use Lpdf\Layout\ImgAttr;
use Lpdf\Layout\Pin;
use Lpdf\Layout\FieldAttr;
use Lpdf\Layout\LinkAttr;
use Lpdf\Layout\RowAttr;
use Lpdf\Layout\ShapeAttr;
use Lpdf\Layout\SpanAttr;
use Lpdf\Layout\TableAttr;
use Lpdf\Layout\TdAttr;
use Lpdf\Layout\ColumnAttr;
use Lpdf\Layout\FrameAttr;
use Lpdf\Layout\TrAttr;
use Lpdf\Layout\TextAttr;
use Lpdf\Markdown\MarkdownAttr;
use Lpdf\Markdown\MarkdownH1Attr;
use Lpdf\Markdown\MarkdownQuoteAttr;
use Lpdf\Markdown\MarkdownStylesAttr;
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

    public function testATextThatCapturesAndOneThatShowsACaptureUseTheSchemaNames(): void
    {
        $source = $this->json(L::text(new TextAttr(capture: 'chapter'), ['x']));
        $sink = $this->json(L::text(new TextAttr(captureRef: 'chapter', captureMode: 'first', captureFallback: 'None'), []));

        self::assertEquals(['capture' => 'chapter'], $source['attrs']);
        self::assertEquals(['capture-ref' => 'chapter', 'capture-mode' => 'first', 'capture-fallback' => 'None'], $sink['attrs']);
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
                    L::column(new ColumnAttr(gap: '12pt'), [
                        L::text(new TextAttr(align: 'right', bold: 'true'), ['Title']),
                        L::text(null, ['Body ', L::span(new SpanAttr(bold: 'true'), ['bold']), ' text']),
                        L::link(new LinkAttr(href: 'https://lpdf.io'), [L::text(null, ['link'])]),
                    ]),
                ]),
                L::layer(new LayerAttr(page: 'each'), [
                    L::rect(new RectAttr(x: '40pt', y: '40pt', width: '100pt', height: '60pt', fill: '#ff0000', radius: '6pt')),
                    L::textAt(
                        new TextAtAttr(x: '40pt', y: '120pt', fontSize: '10pt'),
                        ['Layer ', L::span(new SpanAttr(color: '#0000ff'), ['text'])],
                    ),
                ]),
            ]),
        ]);

        self::assertSame($this->render($built), $this->render(L::toXml($built)));
    }

    public function testAnAliasIsTheSlotItNamesAndRendersTheSameFromTheBuilderAndItsXml(): void
    {
        $build = static fn (string $size) => L::document(
            new DocumentAttr(tokens: new DocumentTokens(aliases: ['caption' => 'xs', 'heading' => 'xl'])),
            [L::section(null, [L::layout(null, [L::text(new TextAttr(fontSize: $size), ['Sized'])])])],
        );

        $caption = $build('caption');
        $pdf = $this->render($caption);

        self::assertSame($pdf, $this->render(L::toXml($caption)));
        self::assertSame($pdf, $this->render($build('xs')));
        self::assertNotSame($pdf, $this->render($build('heading')));
    }

    public function testANameThatIsNeitherASlotNorAnAliasIsRejected(): void
    {
        $built = L::document(
            new DocumentAttr(tokens: new DocumentTokens(aliases: ['caption' => 'xs'])),
            [L::section(null, [L::layout(null, [L::text(new TextAttr(fontSize: 'medum'), ['Sized'])])])],
        );

        $this->expectExceptionMessageMatches("/invalid text size: 'medum': expected a slot from xs to xxl, an alias \\(caption\\)/");
        L::engine()->setLicenseKey('test-key')->render($built);
    }

    public function testAHeightIsTheSlotItNamesAndRendersTheSameFromTheBuilderAndItsXml(): void
    {
        $build = static fn (string $height, ?array $row = null) => L::document(
            $row === null ? null : new DocumentAttr(tokens: new DocumentTokens(height: $row)),
            [L::section(null, [L::layout(null, [L::column(new ColumnAttr(background: '#cccccc', height: $height))])])],
        );

        $slot = $build('m');
        $pdf = $this->render($slot);

        self::assertSame($pdf, $this->render(L::toXml($slot)));
        self::assertSame($pdf, $this->render($build('32pt')));
        self::assertNotSame($pdf, $this->render($build('xl')));

        $row = ['xs' => '10pt', 's' => '20pt', 'm' => '50pt', 'l' => '60pt', 'xl' => '70pt', 'xxl' => '80pt'];
        self::assertSame($this->render($build('50pt')), $this->render($build('m', $row)));
    }

    public function testAHeightThatIsNotASlotALengthFillOrFullIsRejected(): void
    {
        $built = L::document(
            null,
            [L::section(null, [L::layout(null, [L::column(new ColumnAttr(height: 'medum'))])])],
        );

        $this->expectExceptionMessageMatches("/invalid height value: 'medum' — expected full, fill, a slot from xs to xxl or a pt value/");
        L::engine()->setLicenseKey('test-key')->render($built);
    }

    public function testAShapeHasTheAttributesOfItsTypeUnderTheSchemaNames(): void
    {
        $node = L::shape(new ShapeAttr(
            type: 'rect', width: '14pt', height: 'm', radius: '2pt', fill: 'primary',
            stroke: '#000000', strokeWidth: '1pt', strokeDash: '3 2', opacity: '0.5',
        ));

        self::assertSame([
            'type' => 'shape',
            'attrs' => [
                'type' => 'rect', 'width' => '14pt', 'height' => 'm', 'radius' => '2pt', 'fill' => 'primary',
                'stroke' => '#000000', 'stroke-width' => '1pt', 'stroke-dash' => '3 2', 'opacity' => '0.5',
            ],
        ], json_decode(json_encode($node), true));
        self::assertSame('5pt', json_decode(json_encode(L::shape(new ShapeAttr(type: 'circle', r: '5pt'))), true)['attrs']['r']);
    }

    public function testAShapeOfEachTypeIsDrawnAndRendersTheSameFromTheBuilderAndItsXml(): void
    {
        $build = static fn (...$shapes) => L::document(
            null,
            [L::section(null, [L::layout(null, [L::row(new RowAttr(align: 'center'), [...$shapes, L::text(null, ['After'])])])])],
        );

        $without = $this->render($build());
        foreach ([
            L::shape(new ShapeAttr(type: 'rect', width: '60pt', height: '12pt', fill: '#00aa00')),
            L::shape(new ShapeAttr(type: 'circle', r: '8pt', fill: '#00aa00')),
            L::shape(new ShapeAttr(type: 'ellipse', width: '40pt', height: '20pt', fill: '#00aa00')),
        ] as $shape) {
            $document = $build($shape);
            self::assertNotSame($without, $this->render($document));
            self::assertSame($this->render($document), $this->render(L::toXml($document)));
        }
    }

    public function testAKeptTableRowMovesOnWithTheRowAfterItAndRendersTheSameFromTheBuilderAndItsXml(): void
    {
        // The table starts 30pt above the foot of the page, so rows A and B fit and C does not.
        $build = static fn (?TrAttr $keep) => L::document(null, [L::section(null, [L::layout(null, [
            L::frame(new FrameAttr(height: '739pt')),
            L::table(new TableAttr(cols: '1fr'), [
                L::tr(null, [L::td(null, [L::text(null, ['A'])])]),
                L::tr($keep, [L::td(null, [L::text(null, ['B'])])]),
                L::tr(null, [L::td(null, [L::text(null, ['C'])])]),
            ]),
        ])])]);

        $kept = $build(new TrAttr(keep: 'with-next'));

        self::assertSame(['keep' => 'with-next'], json_decode(json_encode(L::tr(new TrAttr(keep: 'with-next'))), true)['attrs']);
        self::assertNotSame($this->render($build(null)), $this->render($kept));
        self::assertSame($this->render($kept), $this->render(L::toXml($kept)));
    }

    public function testATableCellThatSpansColumnsMovesTheCellsAfterItAndRendersTheSameFromTheBuilderAndItsXml(): void
    {
        $build = static fn (?TdAttr $first) => L::document(null, [L::section(null, [L::layout(null, [
            L::table(new TableAttr(cols: '1fr 1fr 1fr'), [
                L::tr(null, [L::td($first, [L::text(null, ['A'])]), L::td(null, [L::text(null, ['B'])])]),
            ]),
        ])])]);

        $spanning = $build(new TdAttr(colspan: '2'));

        self::assertSame(['colspan' => '2'], json_decode(json_encode(L::td(new TdAttr(colspan: '2'))), true)['attrs']);
        self::assertNotSame($this->render($build(null)), $this->render($spanning));
        self::assertSame($this->render($spanning), $this->render(L::toXml($spanning)));

        $this->expectExceptionMessageMatches('/span 4 columns, but the table has 3/');
        $this->render($build(new TdAttr(colspan: '3')));
    }

    public function testATableCellThatSpansRowsMovesTheCellsUnderItAndRendersTheSameFromTheBuilderAndItsXml(): void
    {
        $build = static fn (?TdAttr $first) => L::document(null, [L::section(null, [L::layout(null, [
            L::table(new TableAttr(cols: '1fr 1fr'), [
                L::tr(null, [L::td($first, [L::text(null, ['A'])]), L::td(null, [L::text(null, ['B'])])]),
                L::tr(null, [L::td(null, [L::text(null, ['C'])])]),
            ]),
        ])])]);

        $spanning = $build(new TdAttr(rowspan: '2'));

        self::assertSame(['rowspan' => '2'], json_decode(json_encode(L::td(new TdAttr(rowspan: '2'))), true)['attrs']);
        self::assertNotSame($this->render($build(null)), $this->render($spanning));
        self::assertSame($this->render($spanning), $this->render(L::toXml($spanning)));

        $this->expectExceptionMessageMatches('/a rowspan that runs 1 row past the last row of the table/');
        $this->render($build(new TdAttr(rowspan: '3')));
    }

    public function testAFootnoteIsAnFnChildOfATextAndTheCounterIsAnAttributeOfTheDocumentAndASection(): void
    {
        $text = L::text(null, ['Total', L::fn(null, ['See ', L::span(new SpanAttr(bold: 'true'), ['this']), '.']), ' here']);
        $document = L::document(
            new DocumentAttr(footnoteCounter: FootnoteCounter::Document),
            [L::section(new SectionAttr(footnoteCounter: FootnoteCounter::Page), [L::layout(null, [$text])])],
        );

        $xml = L::toXml($document);
        self::assertMatchesRegularExpression('/<document [^>]*footnote-counter="document"/', $xml);
        self::assertMatchesRegularExpression('/<section [^>]*footnote-counter="page"/', $xml);
        self::assertStringContainsString('<text>Total<fn>See <span bold="true">this</span>.</fn> here</text>', $xml);
        self::assertSame(['type' => 'fn', 'attrs' => [], 'nodes' => ['a']], $this->json(L::fn(null, ['a'])));
    }

    public function testACodeAndACodeSpanAreWrittenAsTheirElementsAndRenderTheSameFromTheBuilderAndItsXml(): void
    {
        $document = L::document(null, [L::section(null, [L::layout(null, [
            L::code(new CodeAttr(tabSize: '2'), ["\nfn a() {\n\tb(", L::span(new SpanAttr(color: '#c0392b'), ['x']), ");\n}\n"]),
            L::text(null, ['Run ', L::codeSpan(new CodeSpanAttr(background: '#ffff00'), ['cargo test']), ' now']),
        ])])]);

        $xml = L::toXml($document);
        self::assertStringContainsString("<code tab-size=\"2\">\nfn a() {\n\tb(<span color=\"#c0392b\">x</span>);\n}\n</code>", $xml);
        self::assertStringContainsString('Run <code-span background="#ffff00">cargo test</code-span> now', $xml);
        self::assertSame('code', $this->json(L::code())['type']);
        self::assertSame('code-span', $this->json(L::codeSpan())['type']);
        self::assertSame($this->render($document), $this->render($xml));
    }

    public function testACodeKeepsItsSpacesAndOneLongerThanAPageGoesOnInTheNextAndABadTabSizeIsRejected(): void
    {
        $build = static fn (string ...$nodes) => L::document(null, [L::section(null, [L::layout(null, [L::code(null, $nodes)])])]);

        self::assertNotSame($this->render($build('a    b')), $this->render($build('a b')));

        $lines = implode("\n", array_map(static fn (int $i) => "line $i", range(1, 150)));
        self::assertGreaterThanOrEqual(2, preg_match_all('~/Type\s*/Page[^s]~', $this->render($build($lines))), 'the code splits across pages');

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/invalid tab-size/');
        $this->render(L::document(null, [L::section(null, [L::layout(null, [L::code(new CodeAttr(tabSize: '0'), ['x'])])])]));
    }

    public function testAListIsWrittenWithItsItemsAndRendersTheSameFromTheBuilderAndItsXml(): void
    {
        $document = L::document(null, [L::section(null, [L::layout(null, [
            L::list(new ListAttr(type: 'number', format: 'lower-alpha', suffix: ')'), [
                L::li(null, [L::text(null, ['One'])]),
                L::li(null, [L::text(null, ['Two']), L::list(new ListAttr(bullet: 'dash'), [L::li(null, [L::text(null, ['Nested'])])])]),
            ]),
        ])])]);

        $xml = L::toXml($document);
        self::assertMatchesRegularExpression('~<list format="lower-alpha" suffix="\\)" type="number">\\s*<li>\\s*<text>One</text>\\s*</li>~', $xml);
        self::assertMatchesRegularExpression('~<list bullet="dash">\\s*<li>\\s*<text>Nested</text>~', $xml);
        self::assertSame('list', $this->json(L::list())['type']);
        self::assertSame('li', $this->json(L::li())['type']);
        self::assertSame($this->render($document), $this->render($xml));
    }

    public function testANumberListWritesItsNumbersAndABulletListRefusesWhatNumbersThem(): void
    {
        $items = static fn () => [L::li(null, [L::text(null, ['A'])]), L::li(null, [L::text(null, ['B'])])];
        $build = static fn (?ListAttr $attrs) => L::document(null, [L::section(null, [L::layout(null, [L::list($attrs, $items())])])]);

        $bullets = $this->render($build(null));
        $numbers = $this->render($build(new ListAttr(type: 'number')));
        $roman   = $this->render($build(new ListAttr(type: 'number', format: 'lower-roman', start: '3')));
        self::assertNotSame($bullets, $numbers);
        self::assertNotSame($numbers, $roman);

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/numbers the items of a list/');
        $this->render($build(new ListAttr(format: 'lower-alpha')));
    }

    public function testAFootnoteIsDrawnAndRendersTheSameFromTheBuilderAndItsXmlAndOneInATableIsRejected(): void
    {
        $build = static fn (...$nodes) => L::document(
            null,
            [L::section(null, [L::layout(null, [L::text(null, ['Total', ...$nodes, ' here'])])])],
        );
        $without = $this->render($build());
        $noted = $build(L::fn(null, ['The note.']));
        self::assertNotSame($without, $this->render($noted));
        self::assertSame($this->render($noted), $this->render(L::toXml($noted)));

        $counted = static fn (string $counter) => L::document(new DocumentAttr(footnoteCounter: $counter), [
            L::section(null, [L::layout(null, [L::text(null, ['One', L::fn(null, ['A.'])])])]),
            L::section(null, [L::layout(null, [L::text(null, ['Two', L::fn(null, ['B.'])])])]),
        ]);
        self::assertNotSame($this->render($counted(FootnoteCounter::Document)), $this->render($counted(FootnoteCounter::Section)));

        $table = L::document(null, [L::section(null, [L::layout(null, [
            L::table(new TableAttr(cols: '1fr'), [L::tr(null, [L::td(null, [L::text(null, ['x', L::fn(null, ['y'])])])])]),
        ])])]);
        $this->expectExceptionMessageMatches('/<fn> is not supported in a table/');
        L::engine()->setLicenseKey('test-key')->render($table);
    }

    public function testACircleWithoutRIsRejectedAndSaysSo(): void
    {
        $built = L::document(null, [L::section(null, [L::layout(null, [L::shape(new ShapeAttr(type: 'circle'))])])]);

        $this->expectExceptionMessageMatches("/a circle needs 'r'/");
        L::engine()->setLicenseKey('test-key')->render($built);
    }

    public function testAGradientOfTheTokensFillsABoxAndRendersTheSameFromTheBuilderAndItsXml(): void
    {
        $build = static fn (string $background) => L::document(
            new DocumentAttr(tokens: new DocumentTokens(gradients: [
                'fade' => ['type' => 'linear', 'from' => '#ff0000', 'to' => '#0000ff', 'angle' => 90],
            ])),
            [L::section(null, [L::layout(null, [L::column(new ColumnAttr(background: $background, height: '60pt'))])])],
        );

        $gradient = $build('gradient:fade');
        $pdf = $this->render($gradient);

        self::assertSame($pdf, $this->render(L::toXml($gradient)));
        self::assertNotSame($pdf, $this->render($build('#336699')));
        self::assertStringContainsString('/ShadingType 2', $pdf);
    }

    public function testBoldTextIsTheBoldFaceOfTheFont(): void
    {
        $doc = static fn (string $attrs): string => '<lpdf version="1"><document><section><layout><text '
            . $attrs . '>Hello</text></layout></section></document></lpdf>';

        self::assertSame($this->render($doc('bold="true"')), $this->render($doc('font="Helvetica-Bold"')));
        self::assertNotSame($this->render($doc('bold="true"')), $this->render($doc('')));
    }

    // -- Bookmarks and the table of contents -----------------------------------------------------

    public function testABookmarkGoesInALayoutAndDirectlyInASection(): void
    {
        $bookmark = $this->json(L::bookmark(new BookmarkAttr(label: 'Methods', level: '2', id: 'methods')));
        self::assertEquals(['type' => 'bookmark', 'attrs' => ['label' => 'Methods', 'level' => '2', 'id' => 'methods']], $bookmark);

        $section = $this->json(L::section(new SectionAttr(type: SectionType::Toc, tocIndentStep: '14pt'), [
            L::bookmark(new BookmarkAttr(label: 'Contents')),
            L::layout(null, []),
        ]));
        self::assertEquals(['type' => 'toc', 'toc-indent-step' => '14pt'], $section['attrs']);
        self::assertSame('bookmark', $section['nodes'][0]['type']);
    }

    public function testBookmarksFillTheOutlineAndRenderLikeTheirXml(): void
    {
        $built = L::document(null, [
            L::section(null, [L::bookmark(new BookmarkAttr(label: 'Cover')), L::layout(null, [L::text(null, ['Cover'])])]),
            L::section(null, [
                L::layout(null, [
                    L::link(new LinkAttr(href: '#methods'), [L::text(null, ['Go to Methods'])]),
                    L::bookmark(new BookmarkAttr(label: 'Methods')),
                    L::text(null, ['Methods']),
                ]),
            ]),
        ]);
        $pdf = $this->render($built);

        self::assertStringContainsString('/Title (Cover)', $pdf);
        self::assertStringContainsString('/Title (Methods)', $pdf);
        self::assertStringContainsString('/D /methods', $pdf);
        self::assertSame($pdf, $this->render(L::toXml($built)));
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

    public function testASectionWithTwoLayoutsIsRejected(): void
    {
        $built = L::document(null, [
            L::section(null, [L::layout(null, [L::text(null, ['a'])]), L::layout(null, [L::text(null, ['b'])])]),
        ]);

        $this->expectExceptionMessageMatches('/at most one <layout>/');
        L::engine()->setLicenseKey('test-key')->render($built);
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

    public function testAFileTheDocumentCarriesIsEmbeddedFromLoadFileOrItsSrc(): void
    {
        $terms = tempnam(sys_get_temp_dir(), 'lpdf-terms');
        file_put_contents($terms, 'Terms of sale');
        try {
            $built = L::document(new DocumentAttr(attachments: [
                new FileAttr(name: 'invoice.xml', ref: 'invoice', mime: 'text/xml', relationship: FileRelationship::Data),
                new FileAttr(name: 'terms.txt', src: $terms, description: 'The terms'),
            ]), [L::section(null, [L::layout(null, [L::text(null, ['Invoice'])])])]);
            $engine = L::engine()->setLicenseKey('test-key')->loadFile('invoice', '<invoice/>');

            $pdf = $engine->render($built);
            self::assertSame(2, substr_count($pdf, '/Type /EmbeddedFile'));
            self::assertStringContainsString('/AFRelationship /Data', $pdf);
            self::assertSame($this->normalised($pdf), $this->normalised($engine->render(L::toXml($built))));
        } finally {
            unlink($terms);
        }
    }

    public function testAFileWhoseSrcCannotBeReadIsAnErrorThatNamesIt(): void
    {
        $built = L::document(new DocumentAttr(attachments: [new FileAttr(name: 'data.csv', src: '/no/such/data.csv')]), [
            L::section(null, [L::layout(null, [L::text(null, ['x'])])]),
        ]);

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches("~file 'data\\.csv'.*/no/such/data\\.csv~");
        L::engine()->setLicenseKey('test-key')->render($built);
    }

    public function testAFileWithNoBytesIsAnErrorThatSaysHowToLoadIt(): void
    {
        $xml = '<lpdf version="1"><document><attachments><file name="a.xml"/></attachments>'
            . '<section><layout><text>x</text></layout></section></document></lpdf>';

        $this->expectExceptionMessageMatches("~loadFile\\('a\\.xml', bytes\\)~");
        L::engine()->setLicenseKey('test-key')->render($xml);
    }

    public function testTheConstantsAreTheSchemaValues(): void
    {
        self::assertSame('data', FileRelationship::Data);
        self::assertSame('text', FieldType::Text);
        self::assertSame('top', Pin::Top);
        self::assertSame('landscape', Orientation::Landscape);
        self::assertSame('Times-Bold', BuiltinFont::TimesBold);
    }

    // -- Helpers ---------------------------------------------------------------------------------

    // -- Markdown --------------------------------------------------------------------------------

    private const MARKDOWN_BODY = "# Release notes\n\nSome *emphasis*, **strong** text and `code`.\n\n- one\n- two\n\n> a quote\n";

    private function markdownDocument(?MarkdownAttr $attrs = null, ?object $styles = null, string $text = self::MARKDOWN_BODY): object
    {
        return L::document(null, [L::section(null, [
            L::markdown($attrs, [...($styles === null ? [] : [$styles]), LMarkdown::content(NoAttr, [$text])]),
        ])]);
    }

    public function testAMarkdownItsStylesItsContentAndTheirRulesAreBuiltWithTheSchemaNames(): void
    {
        $markdown = $this->json(L::markdown(new MarkdownAttr(margin: '54pt', gap: 'm', bookmarks: '2')));
        self::assertSame('markdown', $markdown['type']);
        self::assertEquals(['margin' => '54pt', 'gap' => 'm', 'bookmarks' => '2'], $markdown['attrs']);

        $styles = $this->json(LMarkdown::styles(new MarkdownStylesAttr(src: 'styles.xml'), [LMarkdown::h1(new MarkdownH1Attr(fontSize: 'xl', color: 'muted'))]));
        self::assertSame('markdown-styles', $styles['type']);
        self::assertEquals(['src' => 'styles.xml'], $styles['attrs']);
        self::assertSame('h1', $styles['nodes'][0]['type']);
        self::assertEquals(['font-size' => 'xl', 'color' => 'muted'], $styles['nodes'][0]['attrs']);
        self::assertEquals(['bar-color' => 'primary'], $this->json(LMarkdown::quote(new MarkdownQuoteAttr(barColor: 'primary')))['attrs']);
        self::assertSame('em', $this->json(LMarkdown::em())['type']);
        self::assertSame(['# a'], $this->json(LMarkdown::content(NoAttr, ['# a']))['nodes']);
    }

    public function testAMarkdownIsWrittenAsXmlWithItsMarkdownAsItIsAndRendersTheSameFromTheBuilderAndItsXml(): void
    {
        $document = $this->markdownDocument(
            new MarkdownAttr(gap: '8pt'),
            LMarkdown::styles(NoAttr, [LMarkdown::h1(new MarkdownH1Attr(color: '#ff0000'))]),
            "a < b & c ]]> d\n\n    code\n",
        );

        $xml = L::toXml($document);
        self::assertStringContainsString('<markdown gap="8pt">', $xml);
        self::assertMatchesRegularExpression('~<markdown-styles>\\s*<h1 color="\\#ff0000"\\s*/>\\s*</markdown-styles>~', $xml);
        self::assertStringContainsString('<markdown-content><![CDATA[a < b & c ]]]]><![CDATA[> d', $xml);
        self::assertSame($this->render($document), $this->render($xml));
    }

    public function testAMarkdownRendersTheTextItIsGivenAndARuleThatSetsAColorChangesThePage(): void
    {
        $plain = $this->render($this->markdownDocument());
        self::assertStringStartsWith('%PDF-', $plain);
        self::assertNotSame($plain, $this->render($this->markdownDocument(null, LMarkdown::styles(NoAttr, [LMarkdown::h1(new MarkdownH1Attr(color: '#ff0000'))]))));
        self::assertNotSame($plain, $this->render($this->markdownDocument(null, null, '# Another title')));
    }

    public function testAFileOfRulesIsReadFromTheSrcOfItsStylesOrLoadedByName(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'lpdf-styles-');
        file_put_contents($file, '<markdown-styles><h1 color="#ff0000"/></markdown-styles>');
        try {
            $inline  = $this->render($this->markdownDocument(null, LMarkdown::styles(NoAttr, [LMarkdown::h1(new MarkdownH1Attr(color: '#ff0000'))])));
            $fromSrc = $this->markdownDocument(null, LMarkdown::styles(new MarkdownStylesAttr(src: $file)));
            self::assertSame($inline, $this->render($fromSrc));
            self::assertSame($inline, $this->render(L::toXml($fromSrc)));

            $loaded = L::engine()->setLicenseKey('test-key')->loadMarkdownStyles('theme.xml', '<markdown-styles><h1 color="#ff0000"/></markdown-styles>');
            $pdf = $loaded->render($this->markdownDocument(null, LMarkdown::styles(new MarkdownStylesAttr(src: 'theme.xml'))));
            self::assertSame($inline, $this->normalised($pdf));
        } finally {
            unlink($file);
        }
    }

    public function testAFileOfRulesThatIsNotThereIsAnErrorThatNamesTheSrc(): void
    {
        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/missing-styles\.xml/');
        $this->render($this->markdownDocument(null, LMarkdown::styles(new MarkdownStylesAttr(src: 'missing-styles.xml'))));
    }

    public function testRawHtmlIsLeftOutOfTheMarkdownAndTheWordsInItAreKept(): void
    {
        $withHtml = $this->render($this->markdownDocument(null, null, "fine\n\n<div>kept</div>\n"));
        self::assertSame($this->render($this->markdownDocument(null, null, "fine\n\nkept\n")), $withHtml);
    }

    public function testTheMarkdownComesFromTheDataOfAnXmlDocumentOrTheTextWrittenInIt(): void
    {
        $xml = '<lpdf version="1"><document><section><markdown><markdown-content data-value="body"><![CDATA[# From the text]]></markdown-content></markdown></section></document></lpdf>';
        $engine = L::engine()->setLicenseKey('test-key');
        $fromData = $this->normalised($engine->render($xml, new RenderOptions(data: ['body' => '# From the data'])));
        $fromText = $this->normalised($engine->render($xml));
        self::assertNotSame($fromData, $fromText);
    }

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
