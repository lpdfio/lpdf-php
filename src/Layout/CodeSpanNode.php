<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A code-span inline node: a run of code in a text.
 *
 * @internal Use L::codeSpan() to construct.
 */
final readonly class CodeSpanNode extends Node
{
    /**
     * @param array<string,string|int|float|bool> $attrs
     * @param string[]             $nodes
     */
    public function __construct(
        private array $attrs,
        private array $nodes,
    ) {}

    public function jsonSerialize(): mixed
    {
        return [
            'type'  => 'code-span',
            'attrs' => (object) $this->attrs,
            'nodes' => $this->nodes,
        ];
    }
}
