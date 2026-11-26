<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A series of a chart: one value in each category, in the order of the series. Left out, the chart
 * has one series. data-source repeats it for each item of a list, so the data can set how many
 * series there are. A chart holds 100 series at most, counted after the data is bound. A pie, a
 * donut and a sparkline have one series and take none.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class ChartSeriesAttr
{
    public function __construct(
        /** The series' entry in the legend. */
        public ?string $label = null,
        /**
         * The color of the series' bars, line or area, and its swatch in the legend. Without it,
         * the series takes the next color of a fixed palette of eight, which starts over past the
         * eighth.
         */
        public ?string $color = null,
    ) {}
}
