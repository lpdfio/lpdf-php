<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * An img (image) leaf node.
 *
 * @internal Use Layout::img() to construct.
 */
final readonly class ImgNode extends Node
{
    /** @param array<string,string|int|float|bool> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return [
            'type'  => 'img',
            'attrs' => (object) $this->attrs,
        ];
    }
}
