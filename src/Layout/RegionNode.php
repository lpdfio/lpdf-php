<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A region node, pinned to the top or bottom of the page.
 *
 * @internal Use Layout::region() to construct.
 */
final readonly class RegionNode extends Node
{
    /**
     * @param array<string,string> $attrs   Must include 'pin'.
     * @param Node[]               $nodes
     */
    public function __construct(
        private array $attrs,
        private array $nodes,
    ) {}

    public function jsonSerialize(): mixed
    {
        return [
            'type'  => 'region',
            'attrs' => (object) $this->attrs,
            'nodes' => $this->nodes,
        ];
    }
}
