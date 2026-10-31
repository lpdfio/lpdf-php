<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/** @internal Use L::layer() to construct. */
final readonly class LayerNode extends Node
{
    /**
     * @param array<string,string|int|float|bool> $attrs
     * @param Node[]               $nodes
     */
    public function __construct(
        private array $attrs,
        private array $nodes,
    ) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'layer', 'attrs' => (object) $this->attrs, 'nodes' => $this->nodes];
    }
}
