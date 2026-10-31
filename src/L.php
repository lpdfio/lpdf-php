<?php

declare(strict_types=1);

namespace Lpdf;

use Lpdf\Canvas\BarcodeAtAttr;
use Lpdf\Canvas\BarcodeAtNode;
use Lpdf\Canvas\CircleAttr;
use Lpdf\Canvas\CircleNode;
use Lpdf\Canvas\EllipseAttr;
use Lpdf\Canvas\EllipseNode;
use Lpdf\Canvas\ImgAtAttr;
use Lpdf\Canvas\ImgAtNode;
use Lpdf\Canvas\LayerAttr;
use Lpdf\Canvas\LayerNode;
use Lpdf\Canvas\LineAttr;
use Lpdf\Canvas\LineNode;
use Lpdf\Canvas\Node as CanvasNode;
use Lpdf\Canvas\PathAttr;
use Lpdf\Canvas\PathNode;
use Lpdf\Canvas\RectAttr;
use Lpdf\Canvas\RectNode;
use Lpdf\Canvas\TextAtAttr;
use Lpdf\Canvas\TextAtNode;
use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\WasmRunner;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\DocumentAssets;
use Lpdf\Kit\DocumentTokens;
use Lpdf\Kit\LayoutAttr;
use Lpdf\Kit\PdfDocument;
use Lpdf\Kit\SectionAttr;
use Lpdf\Kit\SectionLayout;
use Lpdf\Kit\SectionNode;
use Lpdf\Layout\BarcodeAttr;
use Lpdf\Layout\BarcodeNode;
use Lpdf\Layout\BookmarkAttr;
use Lpdf\Layout\BookmarkNode;
use Lpdf\Layout\CodeAttr;
use Lpdf\Layout\CodeNode;
use Lpdf\Layout\CodeSpanAttr;
use Lpdf\Layout\CodeSpanNode;
use Lpdf\Layout\ColumnAttr;
use Lpdf\Layout\ContainerNode;
use Lpdf\Layout\DividerAttr;
use Lpdf\Layout\DividerNode;
use Lpdf\Layout\FieldAttr;
use Lpdf\Layout\FieldNode;
use Lpdf\Layout\FrameAttr;
use Lpdf\Layout\GridAttr;
use Lpdf\Layout\ImgAttr;
use Lpdf\Layout\ImgNode;
use Lpdf\Layout\LiAttr;
use Lpdf\Layout\LinkAttr;
use Lpdf\Layout\ListAttr;
use Lpdf\Layout\Node;
use Lpdf\Layout\RegionAttr;
use Lpdf\Layout\RegionNode;
use Lpdf\Layout\RowAttr;
use Lpdf\Layout\ShapeAttr;
use Lpdf\Layout\ShapeNode;
use Lpdf\Layout\SpanAttr;
use Lpdf\Layout\SpanNode;
use Lpdf\Layout\FnNode;
use Lpdf\Layout\TableAttr;
use Lpdf\Layout\TdAttr;
use Lpdf\Layout\TextAttr;
use Lpdf\Layout\TextNode;
use Lpdf\Layout\TheadAttr;
use Lpdf\Layout\TrAttr;
use Lpdf\Markdown\MarkdownAttr;
use Lpdf\Markdown\MarkdownContentNode;
use Lpdf\Markdown\MarkdownNode;
use Lpdf\Markdown\MarkdownStylesNode;
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
        $flatAttrs = self::optionsToAttrs($a, skip: ['assets', 'tokens', 'meta', 'output', 'attachments']);

        if ($a->assets !== null) {
            $flatAttrs['assets'] = $a->assets;
        }
        if ($a->tokens !== null) {
            $flatAttrs['tokens'] = $a->tokens;
        }
        if ($a->meta !== null) {
            $flatAttrs['meta'] = $a->meta;
        }
        if ($a->output !== null) {
            $flatAttrs['output'] = $a->output;
        }
        if ($a->attachments !== null) {
            $flatAttrs['attachments'] = array_map(
                static fn(object $file): array => self::optionsToAttrs($file),
                array_values($a->attachments),
            );
        }

        return new PdfDocument($flatAttrs, $sections);
    }

    /**
     * Build a section (page) node. Its nodes are a bookmark, then layers, then at most one layout, then
     * layers: the layers before the layout paint under it, and the layers after it paint over it.
     *
     * @param array<BookmarkNode|LayerNode|SectionLayout|MarkdownNode> $nodes
     */
    public static function section(?SectionAttr $attrs = null, array $nodes = []): SectionNode
    {
        return new SectionNode(self::optionsToAttrs($attrs), $nodes);
    }

    /** Wrap layout nodes into a layout block for use inside {@see section()}, which keeps them inside its margin. */
    public static function layout(?LayoutAttr $attrs, array $nodes = []): SectionLayout
    {
        return new SectionLayout(self::optionsToAttrs($attrs), $nodes);
    }

    /**
     * Build a markdown block, which takes the place of a layout in a {@see section()} and holds only Markdown: the
     * {@see LMarkdown::styles()} that say how each part looks, which are optional, and one
     * {@see LMarkdown::content()} with the text. What Markdown can't say, such as the page, a header, a footer or
     * a cover, stays in the section around it: layers paint a header and a footer in the margin it leaves.
     *
     * @param array<MarkdownStylesNode|MarkdownContentNode> $nodes
     */
    public static function markdown(?MarkdownAttr $attrs = null, array $nodes = []): MarkdownNode
    {
        return new MarkdownNode(self::optionsToAttrs($attrs), $nodes);
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
    public static function column(?ColumnAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('column', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function row(?RowAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('row', self::optionsToAttrs($attrs), $nodes);
    }

    /** @param Node[] $nodes */
    public static function grid(?GridAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('grid', self::optionsToAttrs($attrs), $nodes);
    }

    /**
     * Build a list node: a list of items, which are li nodes. A bullet list marks each item with a shape; with
     * type 'number' the engine numbers them, in the format given, and the numbers follow the items that
     * data-source makes.
     *
     * @param Node[] $nodes
     */
    public static function list(?ListAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('list', self::optionsToAttrs($attrs), $nodes);
    }

    /**
     * Build an li, an item of a list. Its words go in a text; a list inside it is a list under the item.
     *
     * @param Node[] $nodes
     */
    public static function li(?LiAttr $attrs = null, array $nodes = []): ContainerNode
    {
        return new ContainerNode('li', self::optionsToAttrs($attrs), $nodes);
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
     * @param array<string|SpanNode|CodeSpanNode|FnNode> $nodes
     */
    public static function text(?TextAttr $attrs = null, array $nodes = []): TextNode
    {
        foreach ($nodes as $i => $child) {
            if (!is_string($child) && !$child instanceof SpanNode && !$child instanceof CodeSpanNode && !$child instanceof FnNode) {
                throw new \InvalidArgumentException(
                    "text() child at index $i must be a string, SpanNode, CodeSpanNode or FnNode, got " . get_debug_type($child),
                );
            }
        }
        return new TextNode(self::optionsToAttrs($attrs), $nodes);
    }

    /**
     * Build a code node: code in a box that fills the width and grows with its lines. The strings are kept as
     * they are written: every space, and a new line wherever there is a "\n". A line longer than the box wraps.
     * The spans color parts of the code.
     *
     * @param array<string|SpanNode> $nodes
     */
    public static function code(?CodeAttr $attrs = null, array $nodes = []): CodeNode
    {
        foreach ($nodes as $i => $child) {
            if (!is_string($child) && !$child instanceof SpanNode) {
                throw new \InvalidArgumentException(
                    "code() child at index $i must be a string or SpanNode, got " . get_debug_type($child),
                );
            }
        }
        return new CodeNode(self::optionsToAttrs($attrs), $nodes);
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

    /**
     * Build a code-span inline node: a run of code in a text, in the code font on a tint.
     *
     * @param string[] $nodes
     */
    public static function codeSpan(?CodeSpanAttr $attrs = null, array $nodes = []): CodeSpanNode
    {
        foreach ($nodes as $i => $child) {
            if (!is_string($child)) {
                throw new \InvalidArgumentException(
                    "codeSpan() child at index $i must be a string, got " . get_debug_type($child),
                );
            }
        }
        return new CodeSpanNode(self::optionsToAttrs($attrs), $nodes);
    }

    /**
     * Build a footnote, for the nodes of a text. The engine numbers it and puts a small raised number at its
     * place in the text; its words go at the bottom of the page where that number lands. It has no
     * attributes: pass `NoAttr`. Its words are strings and spans.
     *
     * @param array<string|SpanNode> $nodes
     */
    public static function fn(null $attrs = null, array $nodes = []): FnNode
    {
        foreach ($nodes as $i => $child) {
            if (!is_string($child) && !$child instanceof SpanNode) {
                throw new \InvalidArgumentException(
                    "fn() child at index $i must be a string or SpanNode, got " . get_debug_type($child),
                );
            }
        }
        return new FnNode([], $nodes);
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

    /** Build a shape node: a rect, circle or ellipse that takes its box in the layout, as an image does. */
    public static function shape(ShapeAttr $attrs): ShapeNode
    {
        return new ShapeNode(self::optionsToAttrs($attrs));
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

    /**
     * Build a bookmark: an entry of the PDF's outline and a target for href="#id" links. It marks the node that
     * follows it; in a section's nodes, it marks the section's first page.
     */
    public static function bookmark(BookmarkAttr $attrs): BookmarkNode
    {
        return new BookmarkNode(self::optionsToAttrs($attrs));
    }

    // ── Layers ─────────────────────────────────────────────────────────────────

    /**
     * Build a layer node, which goes directly in a {@see section()}.
     *
     * @param CanvasNode[] $nodes
     */
    public static function layer(?LayerAttr $attrs = null, array $nodes = []): LayerNode
    {
        return new LayerNode(self::optionsToAttrs($attrs), $nodes);
    }

    /** Build a rect in a layer. */
    public static function rect(RectAttr $attrs): RectNode
    {
        return new RectNode(self::optionsToAttrs($attrs));
    }

    /** Build a line in a layer. */
    public static function line(LineAttr $attrs): LineNode
    {
        return new LineNode(self::optionsToAttrs($attrs));
    }

    /** Build an ellipse in a layer. */
    public static function ellipse(EllipseAttr $attrs): EllipseNode
    {
        return new EllipseNode(self::optionsToAttrs($attrs));
    }

    /** Build a circle in a layer. */
    public static function circle(CircleAttr $attrs): CircleNode
    {
        return new CircleNode(self::optionsToAttrs($attrs));
    }

    /** Build a path in a layer from an SVG path string in `d`. */
    public static function path(PathAttr $attrs): PathNode
    {
        return new PathNode(self::optionsToAttrs($attrs));
    }

    /**
     * Build a `text-at`, the text of the layout put at a fixed place.
     *
     * @param array<string|SpanNode|CodeSpanNode> $nodes
     */
    public static function textAt(TextAtAttr $attrs, array $nodes = []): TextAtNode
    {
        return new TextAtNode(self::optionsToAttrs($attrs), $nodes);
    }

    /** Build an `img-at`, an image at a fixed place. It needs `width` or `height`. */
    public static function imgAt(ImgAtAttr $attrs): ImgAtNode
    {
        return new ImgAtNode(self::optionsToAttrs($attrs));
    }

    /** Build a `barcode-at`, a barcode at a fixed place. */
    public static function barcodeAt(BarcodeAtAttr $attrs): BarcodeAtNode
    {
        return new BarcodeAtNode(self::optionsToAttrs($attrs));
    }
}
