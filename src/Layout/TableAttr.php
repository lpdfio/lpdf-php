<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Rows and cells in columns whose widths are set by cols. The thead row repeats at the top of every
 * page, and rows move between pages whole.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class TableAttr
{
    public function __construct(
        /**
         * Column widths, separated by spaces, in fr, pt, mm, in or % units, or auto: for example
         * 2fr 1fr 120pt 20%, or 1fr 40mm. A width that is none of these is an error. An auto column
         * takes the width its cells need on one line, and the auto columns share what the fixed
         * columns (pt, mm, in and %) leave, in proportion to what they need. When they do not fit,
         * each keeps at least its longest word, and when not even those fit, the longest are cut
         * and a short column keeps its width. auto and fr cannot be in one table: use a length or %
         * for the fixed columns. For example auto 90pt 60pt.
         */
        public string $cols,
        /**
         * A grid: a line around every cell, as thickness and color, for example 1pt #cccccc.
         */
        public ?string $border = null,
        /** The background of every other row below the header, or none. */
        public ?string $stripe = null,
        /**
         * The line under the header row, across the table, as thickness and color like border, or
         * none: for example xs line.
         */
        public ?string $headBorder = null,
        /**
         * The line under each row, across the table, as thickness and color like border, or none:
         * for example xs line-muted. Under the header row, head-border takes its place when it is
         * set. A line stops where a cell spans on into the next row.
         */
        public ?string $rowBorder = null,
        public ?string $gap = null,
        public ?string $padding = null,
        public ?string $background = null,
        /**
         * Width of the element. A length fixes it. fill, in a row, takes the width its line leaves
         * after the other children, shared equally if several on the line use fill, and never moves
         * to the next line; in a column it takes the full width.
         */
        public ?string $width = null,
        public ?string $height = null,
        public ?string $paginate = null,
        public ?bool $debug = null,
        /**
         * true lays out the rows that the tr with data-source repeats one page at a time, straight
         * from the data, for reports of tens of thousands of rows and more. The pages are the ones
         * the same table would have without bulk. The rows follow rules, and so do the thead and
         * the other rows: each td holds one text or none, a text holds plain words with no span,
         * link, bookmark, footnote or reference, and sets no widows or orphans, a text without
         * max-lines holds one line and ends in an ellipsis, a td has no rowspan, a row no keep, and
         * nothing in a row has data-source. A row never splits across pages. The table sits
         * directly in the layout, has no data-source of its own and one tr with it, sets its cols
         * in fr, pt or %, has no height, and neither it nor anything in it has paginate. Its
         * document has no table of contents, no reference to a bookmark, no pdfua and no pdfa of
         * level a, and its section's columns are not balanced. Breaking a rule is an error that
         * names it. Default false.
         */
        public ?bool $bulk = null,
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
    ) {}
}
