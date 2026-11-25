<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A category of a chart: a value for each series, and its label.
 *
 * @internal Use LChart::category() to construct.
 */
final readonly class ChartCategoryNode implements \JsonSerializable
{
    /**
     * @param array<string,string|int|float|bool> $attrs
     * @param ChartValueNode[]                    $nodes
     */
    public function __construct(private array $attrs, private array $nodes) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'category', 'attrs' => (object) $this->attrs, 'nodes' => $this->nodes];
    }
}
