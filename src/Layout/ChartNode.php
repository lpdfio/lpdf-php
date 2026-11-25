<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A chart: its attributes, then its series and its categories.
 *
 * @internal Use L::chart() to construct.
 */
final readonly class ChartNode extends Node
{
    /**
     * @param array<string,string|int|float|bool>      $attrs
     * @param array<ChartSeriesNode|ChartCategoryNode> $nodes
     */
    public function __construct(private array $attrs, private array $nodes) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'chart', 'attrs' => (object) $this->attrs, 'nodes' => $this->nodes];
    }
}
