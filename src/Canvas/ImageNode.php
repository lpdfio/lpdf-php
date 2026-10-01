<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * An `img` on the canvas.
 *
 * @internal Use L::imgAt() to construct.
 */
final readonly class ImageNode extends Node
{
    /** @param array<string,string> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'img', 'attrs' => (object) $this->attrs];
    }
}
