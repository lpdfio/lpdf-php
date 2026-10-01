<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * A `rect` on the canvas.
 *
 * @internal Use L::rect() to construct.
 */
final readonly class RectNode extends Node
{
    /** @param array<string,string> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'rect', 'attrs' => (object) $this->attrs];
    }
}
