<?php
declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\L;
use Lpdf\Canvas\BarcodeAtAttr;
use Lpdf\Canvas\CircleAttr;
use Lpdf\Canvas\EllipseAttr;
use Lpdf\Canvas\LayerAttr;
use Lpdf\Canvas\LineAttr;
use Lpdf\Canvas\ImgAtAttr;
use Lpdf\Canvas\PathAttr;
use Lpdf\Canvas\RectAttr;
use Lpdf\Canvas\TextAtAttr;
use Lpdf\Canvas\Transform;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\DocumentTokens;
use Lpdf\Kit\LayoutAttr;
use Lpdf\Kit\PdfDocument;
use Lpdf\Kit\SectionAttr;
use Lpdf\Layout\BarcodeAttr;
use Lpdf\Layout\ColumnAttr;
use Lpdf\Layout\RegionAttr;
use Lpdf\Layout\SpanAttr;
use Lpdf\Layout\TextAttr;
use PHPUnit\Framework\TestCase;

final class CanvasTest extends TestCase
{
    // -- Integration: engine produces a valid PDF ------------------------------------------------

    public function testLayersOutputIsPdf(): void
    {
        $doc   = $this->minimalDoc();
        $bytes = L::engine()->setLicenseKey('test-key')->render($doc);
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testLayersSnapshotMatchesOrIsCreated(): void
    {
        $doc   = $this->comprehensiveDoc();
        $bytes = L::engine()->setLicenseKey('test-key')->render($doc);
        self::assertStringStartsWith('%PDF-', $bytes);
        SnapshotHelper::compareOrUpdate('layers_comprehensive', $bytes);
    }

    // -- Document / section serialisation --------------------------------------------------------

    public function testDocumentSerializesToDocument(): void
    {
        $doc  = L::document(null, [L::section(null, [])]);
        $json = $this->json($doc);

        self::assertSame(1, $json['version']);
        self::assertSame('document', $json['type']);
        self::assertArrayHasKey('nodes', $json);
    }

    public function testDocumentFontAndDebugAreWritten(): void
    {
        $json = $this->json(L::document(new DocumentAttr(font: 'Times-Roman', debug: 'true')));

        self::assertSame('Times-Roman', $json['attrs']['font']);
        self::assertSame('true', $json['attrs']['debug']);
    }

    public function testSectionSerializesToSection(): void
    {
        $json = $this->json(L::section(new SectionAttr(size: 'a4', background: '#ffffff'), []));

        self::assertSame('section', $json['type']);
        self::assertSame('a4', $json['attrs']['size']);
        self::assertSame('#ffffff', $json['attrs']['background']);
    }

    public function testLayoutSerializesItsMargin(): void
    {
        $json = $this->json(L::layout(new LayoutAttr(margin: '20pt 30pt'), []));

        self::assertSame('layout', $json['type']);
        self::assertSame('20pt 30pt', $json['attrs']['margin']);
    }

    public function testALayerGoesDirectlyInTheSection(): void
    {
        $layer   = L::layer(null, [L::rect(new RectAttr(width: '100pt', height: '100pt'))]);
        $section = L::section(null, [$layer]);
        $json    = $this->json($section);

        self::assertSame('section', $json['type']);
        self::assertCount(1, $json['nodes']);
        self::assertSame('layer', $json['nodes'][0]['type']);
        self::assertCount(1, $json['nodes'][0]['nodes']);
    }

    public function testSectionKeepsItsLayersBeforeAndAfterItsLayoutInOrder(): void
    {
        $section = L::section(null, [
            L::layer(new LayerAttr(page: 'first'), [L::rect(new RectAttr(width: '100pt', height: '100pt'))]),
            L::layout(null, [L::text(null, ['Hello'])]),
            L::layer(new LayerAttr(page: 'last'), [L::rect(new RectAttr(width: '50pt', height: '50pt'))]),
        ]);
        $json = $this->json($section);

        // The first layer paints under the layout, the last one over it.
        self::assertSame(['layer', 'layout', 'layer'], array_column($json['nodes'], 'type'));
        self::assertSame('first', $json['nodes'][0]['attrs']['page']);
        self::assertSame('last', $json['nodes'][2]['attrs']['page']);
    }

    public function testThereIsNoCanvas(): void
    {
        self::assertFalse(method_exists(L::class, 'canvas'));
    }

    public function testSectionWithTitleSerializesAttr(): void
    {
        $json = $this->json(L::section(new SectionAttr(title: 'Cover'), []));

        self::assertSame('Cover', $json['attrs']['title']);
    }

    // -- Region serialisation --------------------------------------------------------------------

    public function testRegionSerializesToRegion(): void
    {
        $region = L::region(new RegionAttr(pin: 'top'), [L::text(null, ['Header'])]);
        $json   = $this->json($region);

        self::assertSame('region', $json['type']);
        self::assertSame('top', $json['attrs']['pin']);
        self::assertCount(1, $json['nodes']);
    }

    // -- Layer content serialisation -------------------------------------------------------------

    public function testRectWritesItsAttributesAsGiven(): void
    {
        $json = $this->json(L::rect(new RectAttr(
            width: '100pt', height: '50pt', x: '10pt', y: '20pt', fill: '#ff0000', radius: '5pt',
        )));

        self::assertSame('rect', $json['type']);
        self::assertEquals(
            ['width' => '100pt', 'height' => '50pt', 'x' => '10pt', 'y' => '20pt', 'fill' => '#ff0000', 'radius' => '5pt'],
            $json['attrs'],
        );
    }

    public function testStrokeAttributesUseTheSchemaNames(): void
    {
        $json = $this->json(L::rect(new RectAttr(
            width: '10pt', height: '10pt', stroke: '#000', strokeWidth: '2pt', strokeDash: '4 2', anchor: 'center',
        )));

        self::assertSame('2pt', $json['attrs']['stroke-width']);
        self::assertSame('4 2', $json['attrs']['stroke-dash']);
        self::assertSame('center', $json['attrs']['anchor']);
    }

    public function testLayerShapesAreSizedByWidthAndHeightAndPlacedByXAndY(): void
    {
        foreach ([RectAttr::class, ImgAtAttr::class, TextAtAttr::class] as $class) {
            self::assertFalse(property_exists($class, 'w'), $class);
            self::assertTrue(property_exists($class, 'width'), $class);
        }
        self::assertFalse(property_exists(RectAttr::class, 'h'));
        self::assertTrue(property_exists(RectAttr::class, 'height'));
        foreach ([CircleAttr::class, EllipseAttr::class] as $class) {
            self::assertFalse(property_exists($class, 'cx'), $class);
            self::assertFalse(property_exists($class, 'cy'), $class);
            self::assertTrue(property_exists($class, 'x'), $class);
            self::assertTrue(property_exists($class, 'y'), $class);
        }
    }

    public function testABarcodeIsSizedByWidthAndHeightNotBySize(): void
    {
        self::assertFalse(property_exists(BarcodeAttr::class, 'size'));
        self::assertTrue(property_exists(BarcodeAttr::class, 'width'));
        self::assertTrue(property_exists(BarcodeAttr::class, 'height'));
    }

    public function testBarcodeAtSerializesWithItsSizeAndPosition(): void
    {
        $json = $this->json(L::barcodeAt(new BarcodeAtAttr(type: 'qr', data: 'x', x: '10pt', y: '20pt', width: '60pt')));

        self::assertSame('barcode-at', $json['type']);
        self::assertSame('60pt', $json['attrs']['width']);
    }

    public function testTextAtWritesItsLineLimitsUnderTheSchemaNames(): void
    {
        $json = $this->json(L::textAt(new TextAtAttr(x: '0pt', y: '0pt', width: '80pt', maxLines: '2', ellipsis: 'true', lineHeight: '1.5'), ['Hi']));

        self::assertSame('2', $json['attrs']['max-lines']);
        self::assertSame('true', $json['attrs']['ellipsis']);
        self::assertSame('1.5', $json['attrs']['line-height']);
    }

    public function testALineHeightRowAndATextLineHeightAreWrittenUnderTheirSchemaNames(): void
    {
        $document = $this->json(L::document(new DocumentAttr(tokens: new DocumentTokens(lineHeight: ['m' => '1.2', 'xxl' => '2']))));
        self::assertSame(['m' => '1.2', 'xxl' => '2'], $document['attrs']['tokens']['line-height']);

        self::assertSame('l', $this->json(L::text(new TextAttr(lineHeight: 'l'), ['A token']))['attrs']['line-height']);
        self::assertSame('1.5', $this->json(L::text(new TextAttr(lineHeight: '1.5'), ['A factor']))['attrs']['line-height']);
    }

    public function testAliasesAreATokenRowOfNamesForSlots(): void
    {
        $aliases = ['caption' => 'xs', 'sub-title' => 'l'];
        $document = $this->json(L::document(new DocumentAttr(tokens: new DocumentTokens(aliases: $aliases))));

        self::assertSame($aliases, $document['attrs']['tokens']['aliases']);
    }

    public function testGradientsAreATokenRowOfNamedGradients(): void
    {
        $gradients = [
            'fade' => ['type' => 'linear', 'from' => 'primary', 'to' => '#0f3e8a', 'angle' => '90'],
            'sunset' => ['type' => 'radial', 'stops' => [['offset' => '0%', 'color' => '#f97316'], ['offset' => '100%', 'color' => '#8b5cf6']]],
        ];
        $document = $this->json(L::document(new DocumentAttr(tokens: new DocumentTokens(gradients: $gradients))));

        self::assertSame($gradients, $document['attrs']['tokens']['gradients']);
    }

    public function testALayerGivesItsTextAFontASizeAndAColorAndABoxHasAColorAndAnOpacity(): void
    {
        $layer = $this->json(L::layer(new LayerAttr(font: 'Courier', fontSize: 's', color: 'danger')));
        self::assertSame(['font' => 'Courier', 'font-size' => 's', 'color' => 'danger'], $layer['attrs']);

        $column = $this->json(L::column(new ColumnAttr(color: 'text-muted', opacity: '0.5')));
        self::assertSame(['color' => 'text-muted', 'opacity' => '0.5'], $column['attrs']);
    }

    public function testABoxAndALayerShapeHaveAShadow(): void
    {
        $column = $this->json(L::column(new ColumnAttr(shadow: '2pt 3pt #000000 0.2')));
        self::assertSame(['shadow' => '2pt 3pt #000000 0.2'], $column['attrs']);

        self::assertSame('2pt 3pt #000000', $this->json(L::rect(new RectAttr(width: '50pt', height: '30pt', shadow: '2pt 3pt #000000')))['attrs']['shadow']);
        self::assertSame('-2pt -3pt text', $this->json(L::circle(new CircleAttr(r: '5pt', shadow: '-2pt -3pt text')))['attrs']['shadow']);
        self::assertSame('2pt 3pt #000000 0.5', $this->json(L::ellipse(new EllipseAttr(rx: '5pt', ry: '3pt', shadow: '2pt 3pt #000000 0.5')))['attrs']['shadow']);
    }

    public function testLineSerializesCorrectly(): void
    {
        $json = $this->json(L::line(new LineAttr(x1: '0pt', y1: '0pt', x2: '100pt', y2: '100pt', lineCap: 'round')));

        self::assertSame('line', $json['type']);
        self::assertSame('100pt', $json['attrs']['x2']);
        self::assertSame('round', $json['attrs']['line-cap']);
    }

    public function testEllipseSerializesCorrectly(): void
    {
        $json = $this->json(L::ellipse(new EllipseAttr(rx: '40pt', ry: '20pt', x: '10pt', y: '30pt', fill: '#00ff00')));

        self::assertSame('ellipse', $json['type']);
        self::assertSame('40pt', $json['attrs']['rx']);
        self::assertSame('#00ff00', $json['attrs']['fill']);
    }

    public function testCircleSerializesCorrectly(): void
    {
        $json = $this->json(L::circle(new CircleAttr(r: '30pt', x: '70pt', y: '70pt')));

        self::assertSame('circle', $json['type']);
        self::assertSame('30pt', $json['attrs']['r']);
    }

    public function testPathSerializesCorrectly(): void
    {
        $json = $this->json(L::path(new PathAttr(d: 'M 0 0 L 100 100 Z', fill: '#0000ff', fillRule: 'evenodd')));

        self::assertSame('path', $json['type']);
        self::assertSame('M 0 0 L 100 100 Z', $json['attrs']['d']);
        self::assertSame('evenodd', $json['attrs']['fill-rule']);
    }

    public function testImgSerializesCorrectly(): void
    {
        $json = $this->json(L::imgAt(new ImgAtAttr(name: 'logo', width: '200pt', height: '150pt', x: '10pt', y: '20pt')));

        self::assertSame('img-at', $json['type']);
        self::assertSame('logo', $json['attrs']['name']);
        self::assertSame('200pt', $json['attrs']['width']);
    }

    public function testTextTakesItsAttributesFirstAndItsContentSecond(): void
    {
        $json = $this->json(L::textAt(
            new TextAtAttr(x: '20pt', y: '40pt', font: 'Helvetica', fontSize: '14pt', color: '#333333'),
            ['Hello'],
        ));

        self::assertSame('text-at', $json['type']);
        self::assertSame(['Hello'], $json['nodes']);
        self::assertSame('Helvetica', $json['attrs']['font']);
        self::assertSame('14pt', $json['attrs']['font-size']);
    }

    public function testTextContentCanMixStringsAndSpans(): void
    {
        $json = $this->json(L::textAt(
            new TextAtAttr(x: '0pt', y: '0pt'),
            ['base ', L::span(new SpanAttr(font: 'Helvetica-Bold', color: '#ff0000'), ['bold'])],
        ));

        self::assertSame('base ', $json['nodes'][0]);
        self::assertSame('span', $json['nodes'][1]['type']);
        self::assertSame('Helvetica-Bold', $json['nodes'][1]['attrs']['font']);
    }

    // -- Layer serialisation ---------------------------------------------------------------------

    public function testLayerSerializesWithOpacity(): void
    {
        $json = $this->json(L::layer(new LayerAttr(opacity: '0.5'), [L::rect(new RectAttr(width: '100pt', height: '100pt'))]));

        self::assertSame('layer', $json['type']);
        self::assertSame('0.5', $json['attrs']['opacity']);
        self::assertCount(1, $json['nodes']);
    }

    public function testLayerWithPageScopeSerializesPage(): void
    {
        $json = $this->json(L::layer(new LayerAttr(page: 'each'), []));

        self::assertSame('each', $json['attrs']['page']);
    }

    public function testLayerClipIsWrittenAsGiven(): void
    {
        $json = $this->json(L::layer(new LayerAttr(clip: '10 10 100 50'), []));

        self::assertSame('10 10 100 50', $json['attrs']['clip']);
    }

    public function testLayerTransformAcceptsATransformAsAString(): void
    {
        $transform = new Transform([1.0, 0.0, 0.0, 1.0, 50.0, 100.0]);
        $json      = $this->json(L::layer(new LayerAttr(transform: (string) $transform), []));

        self::assertSame('matrix(1,0,0,1,50,100)', $json['attrs']['transform']);
    }

    public function testUnsetAttributesAreOmitted(): void
    {
        $json = $this->json(L::rect(new RectAttr(width: '50pt', height: '50pt')));

        self::assertArrayNotHasKey('fill', $json['attrs']);
        self::assertArrayNotHasKey('stroke', $json['attrs']);
    }

    // -- Helpers ---------------------------------------------------------------------------------

    /** @return array<string,mixed> */
    private function json(object $node): array
    {
        return json_decode(json_encode($node, JSON_THROW_ON_ERROR), true);
    }

    private function minimalDoc(): PdfDocument
    {
        return L::document(
            null,
            [
                L::section(new SectionAttr(size: 'a4'), [
                    L::layer(null, [
                        L::rect(new RectAttr(x: '40pt', y: '40pt', width: '200pt', height: '100pt', fill: '#4a90e2')),
                        L::textAt(
                            new TextAtAttr(x: '40pt', y: '160pt', font: 'Helvetica', fontSize: '16pt', color: '#000000'),
                            ['Hello Layer!'],
                        ),
                    ]),
                ]),
            ],
        );
    }

    private function comprehensiveDoc(): PdfDocument
    {
        return L::document(
            null,
            [
                L::section(new SectionAttr(size: 'a4'), [
                    L::layer(null, [
                        L::rect(new RectAttr(
                            x: '40pt', y: '40pt', width: '200pt', height: '100pt',
                            fill: '#4a90e2', stroke: '#1a5276', strokeWidth: '2pt', radius: '8pt',
                        )),
                        L::line(new LineAttr(x1: '40pt', y1: '170pt', x2: '555pt', y2: '170pt')),
                        L::ellipse(new EllipseAttr(x: '60pt', y: '200pt', rx: '80pt', ry: '50pt', fill: '#f39c12')),
                        L::circle(new CircleAttr(x: '340pt', y: '190pt', r: '60pt', fill: '#27ae60')),
                        L::path(new PathAttr(d: 'M 40 360 L 200 310 L 360 360 Z', fill: '#8e44ad')),
                        L::textAt(
                            new TextAtAttr(x: '40pt', y: '420pt', font: 'Helvetica', fontSize: '18pt', color: '#1a1a1a'),
                            ['Layer text'],
                        ),
                    ]),
                    L::layer(new LayerAttr(opacity: '0.5'), [
                        L::rect(new RectAttr(x: '40pt', y: '460pt', width: '515pt', height: '60pt', fill: '#e74c3c')),
                    ]),
                ]),
            ],
        );
    }
}
