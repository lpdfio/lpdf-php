<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A code node: code in a box, whose strings keep their spaces and lines.
 *
 * @internal Use L::code() to construct.
 */
final readonly class CodeNode extends Node
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
        return [
            'type'  => 'code',
            'attrs' => (object) $this->attrs,
            'nodes' => $this->nodes,
        ];
    }
}
