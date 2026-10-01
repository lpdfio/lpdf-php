<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * A `path` on the canvas.
 *
 * @internal Use L::path() to construct.
 */
final readonly class PathNode extends Node
{
    /** @param array<string,string> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'path', 'attrs' => (object) $this->attrs];
    }
}
