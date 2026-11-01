<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Fills a fixed number of equal columns from left to right, then starts a new row. A row never
 * splits across pages. For data with a header row, use table.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class GridAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $fontSize = null,
        /**
         * The color of the text inside the box. A text that sets no color of its own takes the
         * nearest box's, and the text token when no box sets one. A divider or a barcode in the box
         * keeps its own color.
         */
        public ?string $color = null,
        public ?string $gap = null,
        /**
         * Space between the edge of the box and its content, written like CSS: one value for all
         * sides, two for top-bottom and left-right, three for top, left-right and bottom, four for
         * top, right, bottom and left. Each value is a length or a name from the space row of the
         * tokens, and they can be mixed: 0pt 0pt 0pt m.
         */
        public ?string $padding = null,
        /**
         * Height of the box. Leave it out to size to the content. A length, or a name from the
         * height row of the tokens, fixes it; fill takes what is left after its siblings (shared
         * equally if several use fill); full takes all the height available. Any of these stops the
         * box splitting across pages.
         */
        public ?string $height = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $radius = null,
        /**
         * How opaque the box and everything in it is, from 0 to 1. The box is faded as one, so
         * children that overlap do not show through each other, and a box inside a faded box is
         * faded further: 0.5 inside 0.5 is drawn at 0.25. A form field in the box is not faded.
         * Default 1.
         */
        public ?float $opacity = null,
        /**
         * A hard-edged shadow: a copy of the box, moved right and down by the first two lengths
         * (negative is left and up) and filled with the color, drawn under the box with the same
         * radius. An opacity from 0 to 1 after the color fades the shadow alone: "2pt 3pt #000000
         * 0.2". There is no blur. The shadow takes no room in the layout, so it can run into the
         * margin or a neighbor, and the siblings after the box are drawn over it. A box with no
         * background shows the shadow only outside it. The box's own opacity fades the box and its
         * shadow together.
         */
        public ?string $shadow = null,
        /**
         * Where the box falls in the page flow. no: never split the box, and move it whole to the
         * next page when it does not fit. break-before: start it on a new page. break-after: start
         * the next sibling on a new page. keep-next: keep it on one page with the sibling that
         * follows, moving both to the next page if that sibling would not fit and cannot start on
         * this page: a table splits after its header and a row, and a text after a few lines, and
         * then it stays with that first part. break-before, break-after and keep-next take effect
         * on the children of layout; on a box inside another box they are not applied. no applies
         * at any depth.
         */
        public ?string $paginate = null,
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
         * pdfa or pdfua on the output, everything shows. With pdfx, the file is the print version:
         * screen content stays in it, off, and a reader can turn it on in a layers panel.
         */
        public ?string $ocg = null,
        /**
         * Width of the element. A length fixes it. fill, in a row, takes the width its line leaves
         * after the other children, shared equally if several on the line use fill, and never moves
         * to the next line; in a column it takes the full width.
         */
        public ?string $width = null,
        public ?string $colWidth = null,
        public ?int $cols = null,
    ) {}
}
