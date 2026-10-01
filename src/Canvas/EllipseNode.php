<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * A `ellipse` on the canvas.
 *
 * @internal Use L::ellipse() to construct.
 */
final readonly class EllipseNode extends Node
{
    /** @param array<string,string> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'ellipse', 'attrs' => (object) $this->attrs];
    }
}
