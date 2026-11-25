<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * The values of the `type` attribute of a chart: the kind of chart. bar draws a bar for each value,
 * line a line through the values of each series, area a line filled down to zero, or stacked, and
 * sparkline a small line with no axis, labels or legend.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class ChartType
{
    public const Bar = 'bar';
    public const Line = 'line';
    public const Area = 'area';
    public const Sparkline = 'sparkline';
}
