<?php

declare(strict_types=1);

namespace Lpdf;

use Lpdf\Canvas\CircleNode;
use Lpdf\Canvas\EllipseNode;
use Lpdf\Canvas\EllipseStyle;
use Lpdf\Canvas\ImageNode;
use Lpdf\Canvas\LayerAttr;
use Lpdf\Canvas\LayerNode;
use Lpdf\Canvas\LineNode;
use Lpdf\Canvas\LineStyle;
use Lpdf\Canvas\Node as CanvasNode;
use Lpdf\Canvas\PathNode;
use Lpdf\Canvas\PathStyle;
use Lpdf\Canvas\RectNode;
use Lpdf\Canvas\RectStyle;
use Lpdf\Canvas\Run;
use Lpdf\Canvas\TextNode as CanvasTextNode;
use Lpdf\Canvas\TextStyle;
use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\WasmRunner;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\PdfDocument;
use Lpdf\Kit\SectionAttr;
use Lpdf\Kit\SectionCanvas;
use Lpdf\Kit\SectionLayout;
use Lpdf\Kit\SectionNode;
use Lpdf\Layout\BarcodeAttr;
use Lpdf\Layout\BarcodeNode;
use Lpdf\Layout\ClusterAttr;
use Lpdf\Layout\ContainerNode;
use Lpdf\Layout\DividerAttr;
use Lpdf\Layout\DividerNode;
use Lpdf\Layout\FieldAttr;
use Lpdf\Layout\FieldNode;
use Lpdf\Layout\FieldType;
use Lpdf\Layout\FlankAttr;
use Lpdf\Layout\FrameAttr;
use Lpdf\Layout\GridAttr;
use Lpdf\Layout\ImgAttr;
use Lpdf\Layout\ImgNode;
use Lpdf\Layout\LinkAttr;
use Lpdf\Layout\Node;
use Lpdf\Layout\Pin;
use Lpdf\Layout\RegionAttr;
use Lpdf\Layout\RegionNode;
use Lpdf\Layout\SpanAttr;
use Lpdf\Layout\SpanNode;
use Lpdf\Layout\SplitAttr;
use Lpdf\Layout\StackAttr;
use Lpdf\Layout\TableAttr;
use Lpdf\Layout\TdAttr;
use Lpdf\Layout\TextAttr;
use Lpdf\Layout\TextNode;
use Lpdf\Layout\TheadAttr;
use Lpdf\Layout\TrAttr;
use Lpdf\Shared\AttrsHelper;

/**
 * Flat entry point for building and rendering lpdf documents.
 *
 * @example
 * ```php
 * use Lpdf\Pdf;
 * use Lpdf\Kit\{DocumentAttr, SectionAttr};
 *
 * $doc = Pdf::document(new DocumentAttr(), [
 *     Pdf::section(new SectionAttr(size: 'a4'), [
 *         Pdf::layout(null, [Pdf::text(null, ['Hello'])]),
 *     ]),
 * ]);
 * $pdf = Pdf::engine()->setLicenseKey('…')->render($doc);
 * ```
 */
final class Pdf
{
    use AttrsHelper;

    // ── Engine ─────────────────────────────────────────────────────────────────

    /** Create a new {@see PdfEngine} instance. */
    public static function engine(?EngineOptions $options = null): PdfEngine
    {
        return new PdfEngine($options ?? new EngineOptions());
    }
    /**
     * Convert a PdfDocument tree to an lpdf XML string.
     *
     * @throws EngineException On serialisation error.
     */
    public static function toXml(PdfDocument $doc): string
    {
        $inputStr = json_encode($doc, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $runner = new WasmRunner(
            wasmBinary: \dirname(__DIR__) . '/resources/lpdf-wasi.wasm',
            wasmRunner: 'wasmtime',
            timeout: 30,
        );
        $payload  = ['method' => 'kit_to_xml', 'key' => '', 'input' => $inputStr];
        $response = $runner->invoke($payload);
        if (!isset($response['xml'])) {
            throw new EngineException('Unexpected response from WASI kit_to_xml call.');
        }
        return $response['xml'];
    }
    // ── Document / section ─────────────────────────────────────────────────────

    /** Build the root document node. */
    public static function document(?DocumentAttr $attrs, array $sections = []): PdfDocument
    {
        $a = $attrs ?? new DocumentAttr();
        $flatAttrs = self::optionsToAttrs($a, skip: ['tokens', 'meta']);

        if ($a->tokens !== null) {
            $flatAttrs['tokens'] = $a->tokens;
        }
        if ($a->meta !== null) {
            $flatAttrs['meta'] = $a->meta;
        }

        return new PdfDocument($flatAttrs, $sections);
    }

    /** Build a section (page) node. */
    public static function section(?SectionAttr $attrs, array $nodes = []): SectionNode
    {
        return new SectionNode(self::optionsToAttrs($attrs), $nodes);
    }

    /** Wrap layout nodes into a layout block for use inside {@see section()}. */
    public static function layout(mixed $attrs, array $nodes = []): SectionLayout
    {
        return new SectionLayout($nodes);
    }

    /** Wrap canvas layer nodes into a canvas block for use inside {@see section()}. */
    public static function canvas(mixed $attrs, array $layers = []): SectionCanvas
    {
        return new SectionCanvas($layers);
    }

    // ── Layout containers ──────────────────────────────────────────────────────

    /** @param Node[] $nodes */
    public static function stack(?StackAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('stack', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function flank(?FlankAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('flank', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function split(?SplitAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('split', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function cluster(?ClusterAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('cluster', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function grid(?GridAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('grid', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function frame(?FrameAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('frame', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function link(?LinkAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('link', self::optionsToAttrs($attrs), $nodes);
    }

    // ── Table ──────────────────────────────────────────────────────────────────

    /** @param Node[] $nodes */
    public static function table(?TableAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('table', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function thead(?TheadAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('thead', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function tr(?TrAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('tr', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function td(?TdAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('td', self::optionsToAttrs($attrs), $nodes);
    }

    // ── Layout leaves ───────────────────────────────────────────────────────────

    /**
     * Build a text paragraph node.
     *
     * @param array<string|SpanNode> $nodes
     */
    public static function text(?TextAttr $attrs, array $nodes = []): TextNode
    {
        foreach ($nodes as $i => $child) {
            if (!is_string($child) && !$child instanceof SpanNode) {
                throw new \InvalidArgumentException(
                    "text() child at index $i must be a string or SpanNode, got " . get_debug_type($child),
                );
            }
        }
        return new TextNode(self::optionsToAttrs($attrs), $nodes);
    }

    /**
     * Build a span inline node.
     *
     * @param string[] $nodes
     */
    public static function span(?SpanAttr $attrs, array $nodes = []): SpanNode
    {
        foreach ($nodes as $i => $child) {
            if (!is_string($child)) {
                throw new \InvalidArgumentException(
                    "span() child at index $i must be a string, got " . get_debug_type($child),
                );
            }
        }
        return new SpanNode(self::optionsToAttrs($attrs), $nodes);
    }

    /** Build a divider (horizontal rule) node. */
    public static function divider(?DividerAttr $attrs = null): DividerNode
    {
        return new DividerNode(self::optionsToAttrs($attrs));
    }

    /** Build a layout img (flow image) node. */
    public static function img(ImgAttr $attrs): ImgNode
    {
        return new ImgNode(self::optionsToAttrs($attrs));
    }

    /** Build a barcode node. */
    public static function barcode(BarcodeAttr $attrs): BarcodeNode
    {
        return new BarcodeNode(self::optionsToAttrs($attrs));
    }

    /**
     * Build a pinned layout-region node.
     *
     * @param Node[] $nodes
     */
    public static function region(RegionAttr $attrs, array $nodes = []): RegionNode
    {
        $flatAttrs = self::optionsToAttrs($attrs);
        return new RegionNode($flatAttrs, $nodes);
    }

    /**
     * Build an interactive form field node.
     *
     * @param FieldType|string $type  Field type.
     * @param string           $name  Unique field name within the document.
     */
    public static function field(FieldType|string $type, string $name, ?FieldAttr $attrs = null): FieldNode
    {
        $flatAttrs = array_merge(
            ['type' => $type instanceof FieldType ? $type->value : $type, 'name' => $name],
            self::optionsToAttrs($attrs),
        );
        return new FieldNode($flatAttrs);
    }

    // ── Canvas ─────────────────────────────────────────────────────────────────

    /**
     * Build a canvas-layer node.
     *
     * @param CanvasNode[] $nodes
     */
    public static function layer(?LayerAttr $attrs, array $nodes = []): LayerNode
    {
        return new LayerNode($nodes, $attrs);
    }

    /** Build a canvas-rect node. */
    public static function rect(float $x, float $y, float $w, float $h, ?RectStyle $style = null): RectNode
    {
        return new RectNode($x, $y, $w, $h, $style);
    }

    /** Build a canvas-line node. */
    public static function line(float $x1, float $y1, float $x2, float $y2, ?LineStyle $style = null): LineNode
    {
        return new LineNode($x1, $y1, $x2, $y2, $style);
    }

    /** Build a canvas-ellipse node. */
    public static function ellipse(float $cx, float $cy, float $rx, float $ry, ?EllipseStyle $style = null): EllipseNode
    {
        return new EllipseNode($cx, $cy, $rx, $ry, $style);
    }

    /** Build a canvas-circle node. */
    public static function circle(float $cx, float $cy, float $r, ?EllipseStyle $style = null): CircleNode
    {
        return new CircleNode($cx, $cy, $r, $style);
    }

    /** Build a canvas-path node from an SVG path string. */
    public static function path(string $d, ?PathStyle $style = null): PathNode
    {
        return new PathNode($d, $style);
    }

    /**
     * Build a canvas-text node at the given coordinates.
     *
     * @param Run[] $runs Optional rich-text runs.
     */
    public static function textAt(float $x, float $y, string $content, ?TextStyle $style = null, array $runs = []): CanvasTextNode
    {
        return new CanvasTextNode($x, $y, $content, $style, $runs);
    }

    /** Build a canvas-image node at (x, y) with dimensions (w \u00d7 h). */
    public static function imgAt(float $x, float $y, float $w, float $h, string $name): ImageNode
    {
        return new ImageNode($x, $y, $w, $h, $name);
    }
}
