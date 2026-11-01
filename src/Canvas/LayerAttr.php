<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Draws shapes, text, images and barcodes at exact positions on the page, independent of the
 * flowing layout: watermarks, stamps, page furniture. Coordinates start at the top left corner of
 * the whole page, not inside the layout's margin, and y grows downward. A layer before the
 * section's layout paints under it, and one after paints over it. It sets what applies to all of
 * its content: which pages it appears on, opacity, transform and clip, and the font, size and color
 * of its text. Layers cannot be nested.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class LayerAttr
{
    public function __construct(
        public ?string $page = null,
        /**
         * How opaque everything in the layer is, from 0 to 1. To fade one element, put it in a
         * layer of its own.
         */
        public ?float $opacity = null,
        public ?string $transform = null,
        /**
         * A rectangle that cuts off what the layer draws outside it: x y w h, in points from the
         * top left corner of the page.
         */
        public ?string $clip = null,
        /**
         * The font of the text-at in the layer that sets none of its own. Defaults to the font of
         * the document.
         */
        public ?string $font = null,
        /**
         * The size of the text-at in the layer that sets none of its own. Defaults to 11pt.
         */
        public ?string $fontSize = null,
        /**
         * The color of the text-at in the layer that sets none of its own. Defaults to the text
         * token.
         */
        public ?string $color = null,
        /**
         * In a PDF/UA document, what the layer shows, which a screen reader reads as one figure at
         * the layer's place in the document: a layer before the layout before it, one after the
         * layout after it. Without alt a layer is decoration, as a header is, and a screen reader
         * skips it with all its text: a watermark, a running title, a logo's letters. A document
         * without pdfua ignores it.
         */
        public ?string $alt = null,
        /**
         * Puts the element, and everything in it, in an optional content group, which the viewer
         * shows or hides. print: printed, and not shown on screen. screen: shown on screen, and not
         * printed. The element keeps its room either way, so nothing moves. An element with a group
         * inside an element with another shows only where both show. Acrobat, Chrome and Firefox
         * switch the groups; Preview and Safari show everything, on screen and in print. Hidden
         * content is still in the file, where it can be selected and copied: it is not a way to
         * keep anything secret. A link in print content is drawn and does nothing, and a form field
         * can't be in print content: use a frame with a border for a box filled in by hand. With
         * pdfa, pdfua or pdfx on the output, everything shows.
         */
        public ?string $ocg = null,
    ) {}
}
