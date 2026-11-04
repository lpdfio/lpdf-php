<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * Lines on every page of a section, for paper to write or draw on, or a grid to place things by:
 * ruled paper, squared paper, or a page cut into equal parts. A section names it with
 * ruling="name", and the lines are drawn under everything else on its pages, over the background.
 * They are decoration, which a screen reader skips.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class Ruling
{
    /**
     * @param list<RulingLines> $lines The sets of lines, at least one, drawn in this order: a later
     * set paints over an earlier one.
     */
    public function __construct(
        public array $lines,
        /**
         * The space the lines fill and a count divides. page, the default, is the whole page up to
         * its trim edge. margin is the area inside the margin of the section's layout or markdown.
         * A step starts at its top left corner, and the lines stop at its edges.
         */
        public ?string $origin = null,
        /**
         * Whether the lines on the edges of that space are drawn: the first one, and the last one
         * where a step lands on the far edge. A count of 12 draws 13 lines with them and 11
         * without. Defaults to true.
         */
        public ?bool $edges = null,
        /**
         * Puts the lines in an optional content group, as ocg does on an element: print, printed
         * and not shown on screen, or screen, shown on screen and not printed, such as a grid to
         * place things by.
         */
        public ?string $ocg = null,
    ) {}
}
