<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A category of a chart: a bar for each series, or a stack of them, with its label beside the axis.
 * It holds a value for each series of the chart, after the data is bound. data-source repeats it
 * for each item of a list. A chart holds 10,000 categories at most, counted after the data is
 * bound.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class ChartCategoryAttr
{
    public function __construct(
        /**
         * The category's label, under its bars, or beside them with direction="horizontal".
         */
        public ?string $label = null,
        /**
         * The color of the category's bar, in a chart of one series, where the series' color is the
         * default.
         */
        public ?string $color = null,
    ) {}
}
