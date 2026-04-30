<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';


use Lpdf\Canvas\Clip;
use Lpdf\Canvas\EllipseStyle;
use Lpdf\Canvas\LayerAttr;
use Lpdf\Canvas\LineCap;
use Lpdf\Canvas\LineJoin;
use Lpdf\Canvas\LineStyle;
use Lpdf\Canvas\PathStyle;
use Lpdf\Canvas\RectStyle;
use Lpdf\Canvas\Run;
use Lpdf\Canvas\TextAlign;
use Lpdf\Canvas\TextStyle;
use Lpdf\Canvas\Transform;
use Lpdf\L;

use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\DocumentMeta;
use Lpdf\Kit\SectionAttr;

$root = __DIR__ . '/../../../../example/';

// ── Engine ────────────────────────────────────────────────────────────────────

$licenseKey = ''; // file_get_contents($root . 'test.lic');
$engine = L::engine()->setLicenseKey($licenseKey);

// Load a font (used for canvas-text nodes that reference it).
$engine->loadFont('montserrat', file_get_contents($root . 'assets/fonts/Montserrat-Regular.ttf'));

// ── Build a canvas document ────────────────────────────────────────────────────
//
// Each section uses absolute x/y coordinates with the origin at the top-left.
// The page is 595 × 842 pt (A4 portrait).

$section1 = L::section(new SectionAttr(size: 'a4'), [
    L::canvas(null, [
        L::layer(null, [

                // ── Heading bar ──────────────────────────────────────────────────────
                L::rect(0, 0, 595, 60, new RectStyle(fill: '#1a3a5c')),

                L::textAt(28, 18, 'lpdf Canvas Primitives', new TextStyle(font: 'Helvetica-Bold', size: 22, color: '#ffffff')),

                // ── Section: rect ────────────────────────────────────────────────────
                L::textAt(28, 80, 'canvas-rect', new TextStyle(font: 'Helvetica-Bold', size: 11, color: '#555555')),

                // Plain fill
                L::rect(28, 96, 120, 60, new RectStyle(fill: '#4a90e2')),

                // Fill + stroke
                L::rect(164, 96, 120, 60, new RectStyle(
                    fill: '#e8f4fd', stroke: '#2980b9', strokeWidth: 2,
                )),

                // Rounded corners
                L::rect(300, 96, 120, 60, new RectStyle(
                    fill: '#d5f5e3', stroke: '#27ae60', strokeWidth: 1, borderRadius: 12,
                )),

                // Stroke only
                L::rect(436, 96, 120, 60, new RectStyle(
                    stroke: '#e74c3c', strokeWidth: 3,
                )),

                // ── Section: line ────────────────────────────────────────────────────
                L::textAt(28, 176, 'canvas-line', new TextStyle(font: 'Helvetica-Bold', size: 11, color: '#555555')),

                // Solid thin
                L::line(28, 192, 300, 192, new LineStyle(stroke: '#333333', strokeWidth: 1)),

                // Thick round cap
                L::line(28, 210, 300, 210, new LineStyle(stroke: '#8e44ad', strokeWidth: 4, lineCap: LineCap::Round)),

                // Dashed
                L::line(28, 228, 300, 228, new LineStyle(stroke: '#e67e22', strokeWidth: 2, strokeDash: [6, 3])),

                // Diagonal
                L::line(340, 192, 567, 240, new LineStyle(stroke: '#16a085', strokeWidth: 2)),

                // ── Section: ellipse / circle ────────────────────────────────────────
                L::textAt(28, 256, 'canvas-ellipse / canvas-circle', new TextStyle(font: 'Helvetica-Bold', size: 11, color: '#555555')),

                // Ellipse filled
                L::ellipse(100, 305, 72, 40, new EllipseStyle(fill: '#f39c12', stroke: '#d68910', strokeWidth: 2)),

                // Circle filled
                L::circle(260, 305, 40, new EllipseStyle(fill: '#27ae60')),

                // Circle stroke only
                L::circle(380, 305, 40, new EllipseStyle(stroke: '#c0392b', strokeWidth: 3)),

                // Ellipse no fill, dashed stroke
                L::ellipse(490, 305, 65, 35, new EllipseStyle(stroke: '#2c3e50', strokeWidth: 1, strokeDash: [4, 2])),

                // ── Section: path ────────────────────────────────────────────────────
                L::textAt(28, 356, 'canvas-path', new TextStyle(font: 'Helvetica-Bold', size: 11, color: '#555555')),

                // Triangle
                L::path('M 28 410 L 128 370 L 228 410 Z', new PathStyle(fill: '#8e44ad', stroke: '#6c3483', strokeWidth: 1)),

                // Open path (chevron)
                L::path('M 250 410 L 310 375 L 370 410', new PathStyle(stroke: '#2980b9', strokeWidth: 3, lineCap: LineCap::Round, lineJoin: LineJoin::Round)),

                // Bezier curve (cubic)
                L::path('M 400 410 C 420 365 500 365 520 410', new PathStyle(stroke: '#16a085', strokeWidth: 2, fill: '#d1f2eb')),

                // ── Section: text ────────────────────────────────────────────────────
                L::textAt(28, 436, 'canvas-text', new TextStyle(font: 'Helvetica-Bold', size: 11, color: '#555555')),

                // Left-aligned (default)
                L::textAt(28, 454, 'Left-aligned text (Helvetica 12)', new TextStyle(font: 'Helvetica', size: 12, color: '#222222')),

                // Centered
                L::textAt(28, 474, 'Centered over 539 pt', new TextStyle(
                    font: 'Helvetica', size: 12, color: '#2980b9', align: TextAlign::Center, width: 539,
                )),

                // Right-aligned
                L::textAt(28, 494, 'Right-aligned over 539 pt', new TextStyle(
                    font: 'Helvetica', size: 12, color: '#8e44ad', align: TextAlign::Right, width: 539,
                )),

                // Custom font
                L::textAt(28, 518, 'Montserrat Regular — custom TTF font', new TextStyle(
                    font: 'montserrat', size: 13, color: '#1a3a5c',
                )),

                // Rich-text runs
                L::textAt(
                    28, 542,
                    'Mixed runs: ',
                    new TextStyle(font: 'Helvetica', size: 12, color: '#333333'),
                    [
                        new Run('normal '),
                        new Run('bold style', font: 'Helvetica-Bold', color: '#e74c3c'),
                        new Run(' and larger', size: 16, color: '#27ae60'),
                    ],
                ),

                // ── Section: layer ───────────────────────────────────────────────────
                L::textAt(28, 570, 'canvas-layer', new TextStyle(font: 'Helvetica-Bold', size: 11, color: '#555555')),

                // Background for the layer demo
                L::rect(28, 586, 539, 80, new RectStyle(fill: '#eaf2ff', stroke: '#aed6f1', strokeWidth: 1)),
                L::textAt(38, 596, 'Background text (behind semi-transparent layer)', new TextStyle(font: 'Helvetica', size: 10, color: '#999999')),

                // Labels for sub-demos (drawn in the base layer)
                L::textAt(28, 680, 'Layer with clip rect:', new TextStyle(font: 'Helvetica-Bold', size: 11, color: '#555555')),
                L::textAt(260, 680, 'Layer with transform (rotate 15°):', new TextStyle(font: 'Helvetica-Bold', size: 11, color: '#555555')),

                // ── Footer rule ──────────────────────────────────────────────────────
                L::line(28, 808, 567, 808, new LineStyle(stroke: '#cccccc', strokeWidth: 0.5)),
                L::textAt(28, 818, 'generated with lpdf.io', new TextStyle(font: 'Helvetica', size: 9, color: '#aaaaaa')),

        ]),

        // Semi-transparent red overlay layer
        L::layer(new LayerAttr(opacity: 0.4), [
            L::rect(28, 586, 539, 80, new RectStyle(fill: '#e74c3c')),
            L::textAt(38, 614, 'Layer at 40% opacity', new TextStyle(font: 'Helvetica-Bold', size: 14, color: '#ffffff')),
        ]),

        // Layer with clip
        L::layer(new LayerAttr(clip: new Clip(40, 700, 160, 60, borderRadius: 8)), [
            L::rect(28, 696, 200, 80, new RectStyle(fill: '#f9e79f', stroke: '#f1c40f', strokeWidth: 2)),
            L::ellipse(128, 736, 90, 30, new EllipseStyle(fill: '#f39c12')),
        ]),

        // Layer with transform (translate + rotate)
        L::layer(new LayerAttr(transform: Transform::rotate(15, 380.0, 720.0)), [
            L::rect(0, 0, 120, 40, new RectStyle(fill: '#d7bde2', stroke: '#8e44ad', strokeWidth: 1, borderRadius: 6)),
            L::textAt(8, 12, 'Rotated layer', new TextStyle(font: 'Helvetica', size: 11, color: '#4a235a')),
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
