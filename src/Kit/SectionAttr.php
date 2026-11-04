<?php

declare(strict_types=1);

namespace Lpdf\Kit;

final readonly class SectionAttr
{
    /**
     * @param string|null $bleed      How far this section's pages reach past their trim edge, such as `5mm`. Overrides
     *                                the document's; `0mm` takes it away.
     * @param int|null    $pageStart  The number of the section's first page, which starts a new run of pages: `1`
     *                                restarts the count. Without it the pages count on from the section before.
     *                                `{page}` prints a page's number, and `{pages}` how many pages its run has.
     * @param string|null $pageFormat How the section's page numbers are written, as a list's `format` is, such
     *                                as `lower-roman`. Without it the section keeps the format before.
     * @param string|null $pagePrefix Text before the section's page numbers, such as `A-`. Without it the section keeps
     *                                the prefix before; `''` takes it away.
     * @param int|null    $columns    Sets the section's flow in this many columns, 1 to 12, each filled like a small
     *                                page, left to right.
     * @param string|null $columnGap  The space between two columns, a space token or a length; `l` by default.
     * @param string|null $columnRule A line in the middle of each gap, as a border, such as `0.5pt #ccc`.
     * @param string|null $columnFill How the last page's columns fill: `ColumnFill::Auto`, in order, the default, or
     *                                `ColumnFill::Balance`, levelled.
     * @param string|null $ruling     The name of a ruling of the tokens, whose lines are drawn on every page of the
     *                                section, under everything else.
     */
    public function __construct(
        public ?string      $size        = null,
        public ?string $orientation = null,
        public ?string      $background  = null,
        public ?string      $title       = null,
        public ?bool        $debug       = null,
        public ?string      $type        = null,
        public ?string      $tocIndentStep = null,
        public ?string      $footnoteCounter = null,
        public ?string      $bleed       = null,
        public ?int         $pageStart   = null,
        public ?string      $pageFormat  = null,
        public ?string      $pagePrefix  = null,
        public ?int         $columns     = null,
        public ?string      $columnGap   = null,
        public ?string      $columnRule  = null,
        public ?string      $columnFill  = null,
        public ?string      $ruling      = null,
    ) {}
}
