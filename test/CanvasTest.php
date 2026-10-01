<?php
declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\L;
use Lpdf\Canvas\CanvasImgAttr;
use Lpdf\Canvas\CanvasTextAttr;
use Lpdf\Canvas\CircleAttr;
use Lpdf\Canvas\EllipseAttr;
use Lpdf\Canvas\LayerAttr;
use Lpdf\Canvas\LineAttr;
use Lpdf\Canvas\PathAttr;
use Lpdf\Canvas\RectAttr;
use Lpdf\Canvas\Transform;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\PdfDocument;
use Lpdf\Kit\SectionAttr;
use Lpdf\Layout\RegionAttr;
use Lpdf\Layout\SpanAttr;
use PHPUnit\Framework\TestCase;

final class CanvasTest extends TestCase
{
    // -- Integration: engine produces a valid PDF ------------------------------------------------

    public function testCanvasOutputIsPdf(): void
    {
        $doc   = $this->minimalDoc();
        $bytes = L::engine()->setLicenseKey('test-key')->render($doc);
        self::assertStringStartsWith('%PDF-', $bytes);
    }

    public function testCanvasSnapshotMatchesOrIsCreated(): void
    {
        $doc   = $this->comprehensiveDoc();
        $bytes = L::engine()->setLicenseKey('test-key')->render($doc);
        self::assertStringStartsWith('%PDF-', $bytes);
        SnapshotHelper::compareOrUpdate('canvas_comprehensive', $bytes);
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
        $json = $this->json(L::section(new SectionAttr(size: 'a4', margin: '20pt'), []));

        self::assertSame('section', $json['type']);
        self::assertSame('a4', $json['attrs']['size']);
        self::assertSame('20pt', $json['attrs']['margin']);
    }

    public function testSectionWithCanvasLayersSerializesKindNodes(): void
    {
        $layer   = L::layer(null, [L::rect(new RectAttr(w: '100pt', h: '100pt'))]);
        $section = L::section(null, [L::canvas(null, [$layer])]);
        $json    = $this->json($section);

        self::assertSame('section', $json['type']);
        self::assertCount(1, $json['nodes']);
        self::assertSame('canvas', $json['nodes'][0]['type']);
        self::assertCount(1, $json['nodes'][0]['nodes']);
    }

    public function testSectionWithBothLayoutAndCanvas(): void
    {
        $layer   = L::layer(null, [L::rect(new RectAttr(w: '100pt', h: '100pt'))]);
        $section = L::section(null, [
            L::layout(null, [L::text(null, ['Hello'])]),
            L::canvas(null, [$layer]),
        ]);
        $json = $this->json($section);

        // Layout first, canvas on top (overlay)
        self::assertSame('layout', $json['nodes'][0]['type']);
        self::assertSame('canvas', $json['nodes'][1]['type']);
    }

    public function testSectionWithCanvasUnderlayOrder(): void
    {
        $layer   = L::layer(null, [L::rect(new RectAttr(w: '100pt', h: '100pt'))]);
        $section = L::section(null, [
            L::canvas(null, [$layer]),
            L::layout(null, [L::text(null, ['Hello'])]),
        ]);
        $json = $this->json($section);

        self::assertSame('canvas', $json['nodes'][0]['type']);
        self::assertSame('layout', $json['nodes'][1]['type']);
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

    // -- Canvas primitive serialisation ----------------------------------------------------------

    public function testRectWritesItsAttributesAsGiven(): void
    {
        $json = $this->json(L::rect(new RectAttr(
            w: '100pt', h: '50pt', x: '10pt', y: '20pt', fill: '#ff0000', radius: '5pt',
        )));

        self::assertSame('rect', $json['type']);
        self::assertEquals(
            ['w' => '100pt', 'h' => '50pt', 'x' => '10pt', 'y' => '20pt', 'fill' => '#ff0000', 'radius' => '5pt'],
            $json['attrs'],
        );
    }

    public function testStrokeAttributesUseTheSchemaNames(): void
    {
        $json = $this->json(L::rect(new RectAttr(
            w: '10pt', h: '10pt', stroke: '#000', strokeWidth: '2pt', strokeDash: '4 2', opacity: '0.5', anchor: 'center',
        )));

        self::assertSame('2pt', $json['attrs']['stroke-width']);
        self::assertSame('4 2', $json['attrs']['stroke-dash']);
        self::assertSame('0.5', $json['attrs']['opacity']);
        self::assertSame('center', $json['attrs']['anchor']);
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
        $json = $this->json(L::ellipse(new EllipseAttr(rx: '40pt', ry: '20pt', cx: '50pt', cy: '50pt', fill: '#00ff00')));

        self::assertSame('ellipse', $json['type']);
        self::assertSame('40pt', $json['attrs']['rx']);
        self::assertSame('#00ff00', $json['attrs']['fill']);
    }

    public function testCircleSerializesCorrectly(): void
    {
        $json = $this->json(L::circle(new CircleAttr(r: '30pt', cx: '100pt', cy: '100pt')));

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
        $json = $this->json(L::imgAt(new CanvasImgAttr(name: 'logo', w: '200pt', h: '150pt', x: '10pt', y: '20pt')));

        self::assertSame('img', $json['type']);
        self::assertSame('logo', $json['attrs']['name']);
        self::assertSame('200pt', $json['attrs']['w']);
    }

    public function testTextTakesItsAttributesFirstAndItsContentSecond(): void
    {
        $json = $this->json(L::textAt(
            new CanvasTextAttr(x: '20pt', y: '40pt', font: 'Helvetica', fontSize: '14pt', color: '#333333'),
            ['Hello'],
        ));

        self::assertSame('text', $json['type']);
        self::assertSame(['Hello'], $json['nodes']);
        self::assertSame('Helvetica', $json['attrs']['font']);
        self::assertSame('14pt', $json['attrs']['font-size']);
    }

    public function testTextContentCanMixStringsAndSpans(): void
    {
        $json = $this->json(L::textAt(
            new CanvasTextAttr(x: '0pt', y: '0pt'),
            ['base ', L::span(new SpanAttr(font: 'Helvetica-Bold', color: '#ff0000'), ['bold'])],
        ));

        self::assertSame('base ', $json['nodes'][0]);
        self::assertSame('span', $json['nodes'][1]['type']);
        self::assertSame('Helvetica-Bold', $json['nodes'][1]['attrs']['font']);
    }

    // -- Layer serialisation ---------------------------------------------------------------------

    public function testLayerSerializesWithOpacity(): void
    {
        $json = $this->json(L::layer(new LayerAttr(opacity: '0.5'), [L::rect(new RectAttr(w: '100pt', h: '100pt'))]));

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
        $json = $this->json(L::rect(new RectAttr(w: '50pt', h: '50pt')));

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
                    L::canvas(null, [
                        L::layer(null, [
                            L::rect(new RectAttr(x: '40pt', y: '40pt', w: '200pt', h: '100pt', fill: '#4a90e2')),
                            L::textAt(
                                new CanvasTextAttr(x: '40pt', y: '160pt', font: 'Helvetica', fontSize: '16pt', color: '#000000'),
                                ['Hello Canvas!'],
                            ),
                        ]),
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
                    L::canvas(null, [
                        L::layer(null, [
                            L::rect(new RectAttr(
                                x: '40pt', y: '40pt', w: '200pt', h: '100pt',
                                fill: '#4a90e2', stroke: '#1a5276', strokeWidth: '2pt', radius: '8pt',
                            )),
                            L::line(new LineAttr(x1: '40pt', y1: '170pt', x2: '555pt', y2: '170pt')),
                            L::ellipse(new EllipseAttr(cx: '140pt', cy: '250pt', rx: '80pt', ry: '50pt', fill: '#f39c12')),
                            L::circle(new CircleAttr(cx: '400pt', cy: '250pt', r: '60pt', fill: '#27ae60')),
                            L::path(new PathAttr(d: 'M 40 360 L 200 310 L 360 360 Z', fill: '#8e44ad')),
                            L::textAt(
                                new CanvasTextAttr(x: '40pt', y: '420pt', font: 'Helvetica', fontSize: '18pt', color: '#1a1a1a'),
                                ['Canvas text'],
                            ),
                        ]),
                        L::layer(new LayerAttr(opacity: '0.5'), [
                            L::rect(new RectAttr(x: '40pt', y: '460pt', w: '515pt', h: '60pt', fill: '#e74c3c')),
                        ]),
                    ]),
                ]),
            ],
        );
    }
}
