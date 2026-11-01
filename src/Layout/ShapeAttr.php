<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A rectangle, a circle or an ellipse in the layout, for a status dot, a color swatch or a progress
 * bar. It takes its box in the flow as an image does, with no children, and is drawn centered in
 * that box, which the parent's align places. A rect and an ellipse need a width and a height, and a
 * circle an r. A shape with no fill and no stroke draws nothing, but keeps its box. A stroke is
 * centered on the edge of the box, so half of it lies outside. To put a shape at a place on the
 * page, use a rect, circle or ellipse in a layer.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class ShapeAttr
{
    public function __construct(
        public string $type,
        /**
         * Width of a rect or an ellipse, a length or a name from the width row of the tokens. A
         * circle takes r instead.
         */
        public ?string $width = null,
        /**
         * Height of a rect or an ellipse, a length or a name from the height row of the tokens. A
         * circle takes r instead.
         */
        public ?string $height = null,
        /** Radius of a circle. Its box is the square of side 2r. */
        public ?string $r = null,
        /** Rounding of the corners of a rect. A circle and an ellipse are round already. */
        public ?string $radius = null,
        public ?string $fill = null,
        public ?string $stroke = null,
        public ?string $strokeWidth = null,
        public ?string $strokeDash = null,
        /** How opaque the shape is, from 0 to 1. Default 1. */
        public ?float $opacity = null,
        public ?bool $debug = null,
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
