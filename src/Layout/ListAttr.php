<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A list of items, each with a marker: a bullet drawn as a shape, or a number the engine gives. The
 * items go top to bottom with gap between them, each one a li, and a list nests inside an item. The
 * words of an item start indent from the list's left edge, at every level. A long item splits
 * across pages through the line the page ends in, and its marker stays with the first part.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class ListAttr
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
        public ?string $opacity = null,
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
        public ?string $debug = null,
        /** bullet marks each item with a shape, number with a number. Default bullet. */
        public ?string $type = null,
        /**
         * How a number list writes its numbers: decimal (1, 2, 3), lower-alpha (a, b, c, then aa,
         * ab), upper-alpha (A, B, C), lower-roman (i, ii, iii) or upper-roman (I, II, III). A
         * number that a letter or a numeral can't write, below 1 or past 3999 for a numeral, is
         * written in decimal. Default decimal.
         */
        public ?string $format = null,
        /**
         * The number of a number list's first item, to continue a list that something cut in two.
         * Default 1.
         */
        public ?string $start = null,
        /**
         * Text before each number of a number list: prefix="(" and suffix=")" write (a). Default
         * none.
         */
        public ?string $prefix = null,
        /**
         * Text after each number of a number list: suffix=")" writes a). Default a full stop, so a
         * number is written 1.
         */
        public ?string $suffix = null,
        /**
         * The shape that marks the items of a bullet list. Without it a list uses a disc, a list
         * inside a bullet list a circle, and one inside that a square, then the three again.
         */
        public ?string $bullet = null,
        /**
         * The width of the marker's column: the words of an item start this far from the list's
         * left edge, and a list inside an item starts there too. The marker is set at the right of
         * its column, so 9. and 10. end together, and a marker wider than the column is not made to
         * fit. The same length at every level. Default 24pt.
         */
        public ?string $indent = null,
        /**
         * Width of the element. A length fixes it. fill, in a row, takes the width its line leaves
         * after the other children, shared equally if several on the line use fill, and never moves
         * to the next line; in a column it takes the full width.
         */
        public ?string $width = null,
    ) {}
}
