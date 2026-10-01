<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * A `line` on the canvas.
 *
 * @internal Use L::line() to construct.
 */
final readonly class LineNode extends Node
{
    /** @param array<string,string> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'line', 'attrs' => (object) $this->attrs];
    }
}
