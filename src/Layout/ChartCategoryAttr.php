<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A category of a chart: a bar for each series, or a stack of them, or a point on each line, or a
 * slice of a pie or a donut, with its label beside the axis, or in the legend of a pie or a donut.
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
         * The category's label, under its bars or its point, or beside the bars with
         * direction="horizontal", or in the legend of a pie or a donut, before the slice's share.
         */
        public ?string $label = null,
        /**
         * The color of the category's bar, in a bar chart of one series, where the series' color is
         * the default, or of its slice in a pie or a donut, where the default is the next color of
         * the palette.
         */
        public ?string $color = null,
    ) {}
}
