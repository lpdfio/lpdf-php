<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Code, or any text whose spaces and lines matter, in a box. The text is kept as it is written:
 * every space, and a new line wherever one is written, except the line break after the start tag
 * and the indent of the line the end tag stands on. A line longer than the box wraps at a space,
 * and a word with none breaks at the last character that fits, so nothing is cut off. A tab fills
 * to the next multiple of tab-size columns. The box fills the width and grows with its lines, in
 * Courier on a light tint with padding m unless font, background and padding say otherwise. It
 * holds text and span children, which color or style parts of it. It splits across pages between
 * lines, and each part is drawn in its own box. A code element is a column around one text, so the
 * text takes its font, size and color from font, font-size and color.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class CodeAttr
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
        /**
         * Space between the edge of the box and its content, written like CSS: one value for all
         * sides, two for top-bottom and left-right, three for top, left-right and bottom, four for
         * top, right, bottom and left.
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
         * follows, moving both to the next page if that sibling would not fit. break-before,
         * break-after and keep-next take effect on the children of layout; on a box inside another
         * box they are not applied. no applies at any depth.
         */
        public ?string $paginate = null,
        public ?string $debug = null,
        /**
         * Width of the element. A length fixes it. fill, in a row, takes the width its line leaves
         * after the other children, shared equally if several on the line use fill, and never moves
         * to the next line; in a column it takes the full width.
         */
        public ?string $width = null,
        /**
         * How many columns a tab fills to: it moves the next character to the next multiple of this
         * many characters from the start of the line. Default 4.
         */
        public ?string $tabSize = null,
        /**
         * The distance between lines, as a factor of the font size: a number above 0, such as 1.5,
         * or a name from the line-height row of the tokens. Default 1.2, which is the token m.
         */
        public ?string $lineHeight = null,
        /**
         * The fewest lines of the code that may stand alone at the top of the next page when it
         * splits. Default 2. If fewer would be left, lines move from the first page to the next; 1
         * turns the protection off.
         */
        public ?string $widows = null,
        /**
         * The fewest lines of the code that may stand alone at the bottom of a page when it splits.
         * Default 2. If fewer would fit, the whole code moves to the next page; 1 turns the
         * protection off.
         */
        public ?string $orphans = null,
    ) {}
}
