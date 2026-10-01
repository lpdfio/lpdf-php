<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Lpdf\Canvas\CanvasTextAttr;
use Lpdf\Canvas\CircleAttr;
use Lpdf\Canvas\EllipseAttr;
use Lpdf\Canvas\LayerAttr;
use Lpdf\Canvas\LineAttr;
use Lpdf\Canvas\PathAttr;
use Lpdf\Canvas\RectAttr;
use Lpdf\Canvas\Transform;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\DocumentMeta;
use Lpdf\Kit\SectionAttr;
use Lpdf\L;
use Lpdf\Layout\SpanAttr;

use const Lpdf\NoAttr;

$root = __DIR__ . '/../../../../example/';

// ── Engine ────────────────────────────────────────────────────────────────────

$licenseKey = ''; // file_get_contents($root . 'test.lic');
$engine = L::engine()->setLicenseKey($licenseKey);

// ── Build a canvas document ────────────────────────────────────────────────────
//
// Each shape has an absolute x/y position with the origin at the top-left of the page, written with the
// attributes the schema gives the canvas elements. The page is 595 × 842 pt (A4 portrait).

// The label above each group of shapes.
$label = static fn (string $y) => new CanvasTextAttr(x: '28pt', y: $y, font: 'Helvetica-Bold', fontSize: '11pt', color: '#555555');

$section1 = L::section(new SectionAttr(size: 'a4'), [
    L::canvas(NoAttr, [
        L::layer(NoAttr, [

            // ── Heading bar ──────────────────────────────────────────────────────
            L::rect(new RectAttr(x: '0pt', y: '0pt', w: '595pt', h: '60pt', fill: '#1a3a5c')),

            L::textAt(
                new CanvasTextAttr(x: '28pt', y: '18pt', font: 'Helvetica-Bold', fontSize: '22pt', color: '#ffffff'),
                ['lpdf Canvas Primitives'],
            ),

            // ── Section: rect ────────────────────────────────────────────────────
            L::textAt($label('80pt'), ['rect']),

            // Plain fill
            L::rect(new RectAttr(x: '28pt', y: '96pt', w: '120pt', h: '60pt', fill: '#4a90e2')),

            // Fill + stroke
            L::rect(new RectAttr(
                x: '164pt', y: '96pt', w: '120pt', h: '60pt',
                fill: '#e8f4fd', stroke: '#2980b9', strokeWidth: '2pt',
            )),

            // Rounded corners
            L::rect(new RectAttr(
                x: '300pt', y: '96pt', w: '120pt', h: '60pt',
                fill: '#d5f5e3', stroke: '#27ae60', strokeWidth: '1pt', radius: '12pt',
            )),

            // Stroke only
            L::rect(new RectAttr(x: '436pt', y: '96pt', w: '120pt', h: '60pt', stroke: '#e74c3c', strokeWidth: '3pt')),

            // ── Section: line ────────────────────────────────────────────────────
            L::textAt($label('176pt'), ['line']),

            // Solid thin
            L::line(new LineAttr(x1: '28pt', y1: '192pt', x2: '300pt', y2: '192pt', stroke: '#333333', strokeWidth: '1pt')),

            // Thick round cap
            L::line(new LineAttr(
                x1: '28pt', y1: '210pt', x2: '300pt', y2: '210pt',
                stroke: '#8e44ad', strokeWidth: '4pt', lineCap: 'round',
            )),

            // Dashed
            L::line(new LineAttr(
                x1: '28pt', y1: '228pt', x2: '300pt', y2: '228pt',
                stroke: '#e67e22', strokeWidth: '2pt', strokeDash: '6 3',
            )),

            // Diagonal
            L::line(new LineAttr(x1: '340pt', y1: '192pt', x2: '567pt', y2: '240pt', stroke: '#16a085', strokeWidth: '2pt')),

            // ── Section: ellipse / circle ────────────────────────────────────────
            L::textAt($label('256pt'), ['ellipse / circle']),

            // Ellipse filled
            L::ellipse(new EllipseAttr(
                cx: '100pt', cy: '305pt', rx: '72pt', ry: '40pt',
                fill: '#f39c12', stroke: '#d68910', strokeWidth: '2pt',
            )),

            // Circle filled
            L::circle(new CircleAttr(cx: '260pt', cy: '305pt', r: '40pt', fill: '#27ae60')),

            // Circle stroke only
            L::circle(new CircleAttr(cx: '380pt', cy: '305pt', r: '40pt', stroke: '#c0392b', strokeWidth: '3pt')),

            // Ellipse no fill, dashed stroke
            L::ellipse(new EllipseAttr(
                cx: '490pt', cy: '305pt', rx: '65pt', ry: '35pt',
                stroke: '#2c3e50', strokeWidth: '1pt', strokeDash: '4 2',
            )),

            // ── Section: path ────────────────────────────────────────────────────
            L::textAt($label('356pt'), ['path']),

            // Triangle
            L::path(new PathAttr(d: 'M 28 410 L 128 370 L 228 410 Z', fill: '#8e44ad', stroke: '#6c3483', strokeWidth: '1pt')),

            // Open path (chevron)
            L::path(new PathAttr(d: 'M 250 410 L 310 375 L 370 410', stroke: '#2980b9', strokeWidth: '3pt', lineCap: 'round')),

            // Bezier curve (cubic)
            L::path(new PathAttr(d: 'M 400 410 C 420 365 500 365 520 410', stroke: '#16a085', strokeWidth: '2pt', fill: '#d1f2eb')),

            // ── Section: text ────────────────────────────────────────────────────
            L::textAt($label('436pt'), ['text']),

            // Left-aligned (default)
            L::textAt(
                new CanvasTextAttr(x: '28pt', y: '454pt', font: 'Helvetica', fontSize: '12pt', color: '#222222'),
                ['Left-aligned text (Helvetica 12)'],
            ),

            // Centered
            L::textAt(
                new CanvasTextAttr(
                    x: '28pt', y: '474pt', font: 'Helvetica', fontSize: '12pt', color: '#2980b9',
                    align: 'center', w: '539pt',
                ),
                ['Centered over 539 pt'],
            ),

            // Right-aligned
            L::textAt(
                new CanvasTextAttr(
                    x: '28pt', y: '494pt', font: 'Helvetica', fontSize: '12pt', color: '#8e44ad',
                    align: 'right', w: '539pt',
                ),
                ['Right-aligned over 539 pt'],
            ),

            // Rich-text runs: a span sets the font or colour of part of the text
            L::textAt(
                new CanvasTextAttr(x: '28pt', y: '518pt', font: 'Helvetica', fontSize: '12pt', color: '#333333'),
                [
                    'Mixed runs: normal ',
                    L::span(new SpanAttr(font: 'Helvetica-Bold', color: '#e74c3c'), ['bold style']),
                    ' and ',
                    L::span(new SpanAttr(color: '#27ae60'), ['green']),
                ],
            ),

            // ── Section: layer ───────────────────────────────────────────────────
            L::textAt($label('546pt'), ['layer']),

            // Background for the layer demo
            L::rect(new RectAttr(x: '28pt', y: '562pt', w: '539pt', h: '80pt', fill: '#eaf2ff', stroke: '#aed6f1', strokeWidth: '1pt')),
            L::textAt(
                new CanvasTextAttr(x: '38pt', y: '572pt', font: 'Helvetica', fontSize: '10pt', color: '#999999'),
                ['Background text (behind semi-transparent layer)'],
            ),

            // Label for the transform demo (drawn in the base layer)
            L::textAt(
                new CanvasTextAttr(x: '260pt', y: '660pt', font: 'Helvetica-Bold', fontSize: '11pt', color: '#555555'),
                ['Layer with transform (rotate 15°):'],
            ),

            // ── Footer rule ──────────────────────────────────────────────────────
            L::line(new LineAttr(x1: '28pt', y1: '808pt', x2: '567pt', y2: '808pt', stroke: '#cccccc', strokeWidth: '0.5pt')),
            L::textAt(
                new CanvasTextAttr(x: '28pt', y: '818pt', font: 'Helvetica', fontSize: '9pt', color: '#aaaaaa'),
                ['generated with lpdf.io'],
            ),

        ]),

        // Semi-transparent red overlay layer
        L::layer(new LayerAttr(opacity: '0.4'), [
            L::rect(new RectAttr(x: '28pt', y: '562pt', w: '539pt', h: '80pt', fill: '#e74c3c')),
            L::textAt(
                new CanvasTextAttr(x: '38pt', y: '590pt', font: 'Helvetica-Bold', fontSize: '14pt', color: '#ffffff'),
                ['Layer at 40% opacity'],
            ),
        ]),

        // Layer with transform (rotate around a point)
        L::layer(new LayerAttr(transform: (string) Transform::rotate(15, 380.0, 720.0)), [
            L::rect(new RectAttr(x: '0pt', y: '0pt', w: '120pt', h: '40pt', fill: '#d7bde2', stroke: '#8e44ad', strokeWidth: '1pt', radius: '6pt')),
            L::textAt(
                new CanvasTextAttr(x: '8pt', y: '12pt', font: 'Helvetica', fontSize: '11pt', color: '#4a235a'),
                ['Rotated layer'],
            ),
        ]),
    ]),
]);

// ── Assemble & render ─────────────────────────────────────────────────────────

$doc = L::document(
    new DocumentAttr(meta: new DocumentMeta(title: 'lpdf Canvas Primitives', author: 'lpdf.io')),
    [$section1],
);

$pdf = $engine->render($doc);

$outputFile = 'example-canvas-php.pdf';
file_put_contents($root . "result/{$outputFile}", $pdf);

echo "output: $outputFile (" . number_format(strlen($pdf)) . " bytes)\n";
