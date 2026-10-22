<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A shape leaf node: a rect, circle or ellipse that takes its box in the layout, as an image does.
 *
 * @internal Use L::shape() to construct.
 */
final readonly class ShapeNode extends Node
{
    /** @param array<string,string> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return [
            'type'  => 'shape',
            'attrs' => (object) $this->attrs,
        ];
    }
}
