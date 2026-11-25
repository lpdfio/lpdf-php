<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A series of a chart: its label and color in the legend.
 *
 * @internal Use LChart::series() to construct.
 */
final readonly class ChartSeriesNode implements \JsonSerializable
{
    /** @param array<string,string|int|float|bool> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'series', 'attrs' => (object) $this->attrs];
    }
}
