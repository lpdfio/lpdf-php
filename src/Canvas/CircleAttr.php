<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `circle` element in a layer.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class CircleAttr
{
    public function __construct(
        public string $r,
        /**
         * Where the circle's box goes, the square of side 2r around it: its left edge, or with
         * anchor, an offset from that point of the page, where the box's matching point goes. The
         * centre is r further in.
         */
        public ?string $x = null,
        /**
         * Where the circle's box goes, the square of side 2r around it: its top edge, or with
         * anchor, an offset from that point of the page, where the box's matching point goes. The
         * centre is r further in.
         */
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $fill = null,
        public ?string $stroke = null,
        public ?string $strokeWidth = null,
        public ?string $strokeDash = null,
        /**
         * A hard-edged shadow: a copy of the shape, moved right and down by the first two lengths
         * (negative is left and up) and filled with the color, drawn under the shape. An opacity
         * from 0 to 1 after the color fades the shadow alone: "2pt 3pt #000000 0.2". There is no
         * blur. A shape with no fill shows the shadow only outside it.
         */
        public ?string $shadow = null,
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
