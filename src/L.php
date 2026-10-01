<?php

declare(strict_types=1);

namespace Lpdf;

use Lpdf\Canvas\CanvasImgAttr;
use Lpdf\Canvas\CanvasTextAttr;
use Lpdf\Canvas\CircleAttr;
use Lpdf\Canvas\CircleNode;
use Lpdf\Canvas\EllipseAttr;
use Lpdf\Canvas\EllipseNode;
use Lpdf\Canvas\ImageNode;
use Lpdf\Canvas\LayerAttr;
use Lpdf\Canvas\LayerNode;
use Lpdf\Canvas\LineAttr;
use Lpdf\Canvas\LineNode;
use Lpdf\Canvas\Node as CanvasNode;
use Lpdf\Canvas\PathAttr;
use Lpdf\Canvas\PathNode;
use Lpdf\Canvas\RectAttr;
use Lpdf\Canvas\RectNode;
use Lpdf\Canvas\TextNode as CanvasTextNode;
use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\WasmRunner;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\DocumentAssets;
use Lpdf\Kit\DocumentTokens;
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
use Lpdf\Layout\FlankAttr;
use Lpdf\Layout\FrameAttr;
use Lpdf\Layout\GridAttr;
use Lpdf\Layout\ImgAttr;
use Lpdf\Layout\ImgNode;
use Lpdf\Layout\LinkAttr;
use Lpdf\Layout\Node;
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
 * use Lpdf\L;
 * use Lpdf\Kit\{DocumentAttr, SectionAttr};
 *
 * $doc = L::document(new DocumentAttr(), [
 *     L::section(new SectionAttr(size: 'a4'), [
 *         L::layout(null, [L::text(null, ['Hello'])]),
 *     ]),
 * ]);
 * $pdf = L::engine()->setLicenseKey('…')->render($doc);
 * ```
 */
final class L
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
    public static function document(?DocumentAttr $attrs = null, array $sections = []): PdfDocument
    {
        $a = $attrs ?? new DocumentAttr();
        $flatAttrs = self::optionsToAttrs($a, skip: ['assets', 'tokens', 'meta']);

        if ($a->assets !== null) {
            $flatAttrs['assets'] = $a->assets;
        }
        if ($a->tokens !== null) {
            $flatAttrs['tokens'] = $a->tokens;
        }
        if ($a->meta !== null) {
            $flatAttrs['meta'] = $a->meta;
        }

        return new PdfDocument($flatAttrs, $sections);
    }

    /** Build a section (page) node. */
    public static function section(?SectionAttr $attrs = null, array $nodes = []): SectionNode
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

    /** Create a {@see DocumentAssets} instance (convenience factory). */
    public static function assets(DocumentAssets $attrs): DocumentAssets
    {
        return $attrs;
    }

    /** Create a {@see DocumentTokens} instance (convenience factory). */
    public static function tokens(DocumentTokens $attrs): DocumentTokens
    {
        return $attrs;
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
    public static function link(LinkAttr $attrs, array $nodes = []): ContainerNode
    {
        return new ContainerNode('link', self::optionsToAttrs($attrs), $nodes);
    }

    // ── Table ──────────────────────────────────────────────────────────────────

    /** @param Node[] $nodes */
    public static function table(TableAttr $attrs, array $nodes = []): ContainerNode
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
    public static function text(?TextAttr $attrs = null, array $nodes = []): TextNode
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
    public static function span(?SpanAttr $attrs = null, array $nodes = []): SpanNode
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
     * Build a pinned region node.
     *
     * @param Node[] $nodes
     */
    public static function region(RegionAttr $attrs, array $nodes = []): RegionNode
    {
        return new RegionNode(self::optionsToAttrs($attrs), $nodes);
    }

    /** Build an interactive form field node. The attributes carry its type and its name. */
    public static function field(FieldAttr $attrs): FieldNode
    {
        return new FieldNode(self::optionsToAttrs($attrs));
    }

    // ── Canvas ─────────────────────────────────────────────────────────────────

    /**
     * Build a canvas layer node.
     *
     * @param CanvasNode[] $nodes
     */
    public static function layer(?LayerAttr $attrs = null, array $nodes = []): LayerNode
    {
        return new LayerNode(self::optionsToAttrs($attrs), $nodes);
    }

    /** Build a rect on the canvas. */
    public static function rect(RectAttr $attrs): RectNode
    {
        return new RectNode(self::optionsToAttrs($attrs));
    }

    /** Build a line on the canvas. */
    public static function line(LineAttr $attrs): LineNode
    {
        return new LineNode(self::optionsToAttrs($attrs));
    }

    /** Build an ellipse on the canvas. */
    public static function ellipse(EllipseAttr $attrs): EllipseNode
    {
        return new EllipseNode(self::optionsToAttrs($attrs));
    }

    /** Build a circle on the canvas. */
    public static function circle(CircleAttr $attrs): CircleNode
    {
        return new CircleNode(self::optionsToAttrs($attrs));
    }

    /** Build a path on the canvas from an SVG path string in `d`. */
    public static function path(PathAttr $attrs): PathNode
    {
        return new PathNode(self::optionsToAttrs($attrs));
    }

    /**
     * Build text on the canvas.
     *
     * @param array<string|SpanNode> $nodes
     */
    public static function textAt(CanvasTextAttr $attrs, array $nodes = []): CanvasTextNode
    {
        return new CanvasTextNode(self::optionsToAttrs($attrs), $nodes);
    }

    /** Build an image on the canvas. */
    public static function imgAt(CanvasImgAttr $attrs): ImageNode
    {
        return new ImageNode(self::optionsToAttrs($attrs));
    }
}
