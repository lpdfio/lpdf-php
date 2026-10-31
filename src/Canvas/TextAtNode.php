<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

use Lpdf\Layout\SpanNode;

/**
 * A `text-at` in a layer. Its content is strings and `span` nodes, as in a layout `text`.
 *
 * @internal Use L::textAt() to construct.
 */
final readonly class TextAtNode extends Node
{
    /**
     * @param array<string,string|int|float|bool>   $attrs
     * @param array<string|SpanNode> $nodes
     */
    public function __construct(
        private array $attrs,
        private array $nodes,
    ) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'text-at', 'attrs' => (object) $this->attrs, 'nodes' => $this->nodes];
    }
}
