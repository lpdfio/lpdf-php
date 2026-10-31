<?php

declare(strict_types=1);

namespace Lpdf\Kit;

use Lpdf\Layout\Node;

/**
 * A layout block inside a section — wraps layout nodes.
 *
 * @internal Use Kit::layout() to construct.
 */
final readonly class SectionLayout implements \JsonSerializable
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
        return ['type' => 'layout', 'attrs' => (object) $this->attrs, 'nodes' => $this->nodes];
    }
}
