<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A footnote inline node.
 *
 * @internal Use L::fn() to construct.
 */
final readonly class FnNode extends Node
{
    /**
     * @param array<string,string|int|float|bool> $attrs
     * @param array<string|SpanNode> $nodes
     */
    public function __construct(
        private array $attrs,
        private array $nodes,
    ) {}

    public function jsonSerialize(): mixed
    {
        return [
            'type'  => 'fn',
            'attrs' => (object) $this->attrs,
            'nodes' => $this->nodes,
        ];
    }
}
