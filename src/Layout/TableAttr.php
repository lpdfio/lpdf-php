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
         * Column widths, separated by spaces, in fr, pt or % units: for example 2fr 1fr 120pt 20%.
         */
        public string $cols,
        public ?string $border = null,
        public ?string $stripe = null,
        public ?string $gap = null,
        public ?string $padding = null,
        public ?string $background = null,
        public ?string $width = null,
        public ?string $height = null,
        public ?string $debug = null,
    ) {}
}
