<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A value of a category, as plain text: a number, or nothing for no value.
 *
 * @internal Use LChart::value() to construct.
 */
final readonly class ChartValueNode implements \JsonSerializable
{
    /** @param string[] $nodes */
    public function __construct(private array $nodes) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'value', 'attrs' => (object) [], 'nodes' => $this->nodes];
    }
}
