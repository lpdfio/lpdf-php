<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * A `circle` in a layer.
 *
 * @internal Use L::circle() to construct.
 */
final readonly class CircleNode extends Node
{
    /** @param array<string,string|int|float|bool> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'circle', 'attrs' => (object) $this->attrs];
    }
}
