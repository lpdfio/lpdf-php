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
        public ?string $gap = null,
        /**
         * Space between the edge of the box and its content, written like CSS: one value for all
         * sides, two for top-bottom and left-right, three for top, left-right and bottom, four for
         * top, right, bottom and left.
         */
        public ?string $padding = null,
        /**
         * Height of the box. Leave it out to size to the content. A length fixes it; fill takes
         * what is left after its siblings (shared equally if several use fill); full takes all the
         * height available. Any of these stops the box splitting across pages.
         */
        public ?string $height = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $radius = null,
        public ?string $debug = null,
        public ?string $width = null,
        public ?string $colWidth = null,
        public ?string $cols = null,
    ) {}
}
