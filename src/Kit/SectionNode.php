<?php

declare(strict_types=1);

namespace Lpdf\Kit;

use Lpdf\Layout\BookmarkNode;

/**
 * A section node. Children are {@see SectionLayout} and/or {@see SectionCanvas}
 * in paint order (first child is painted first, last child is on top), and
 * optionally a {@see BookmarkNode} that marks the section's first page.
 *
 * @internal Use Kit::section() to construct.
 */
final readonly class SectionNode implements \JsonSerializable
{
    /**
     * @param array<string,string>                          $attrs
     * @param array<BookmarkNode|SectionLayout|SectionCanvas> $nodes  In paint order.
     */
    public function __construct(
        private array $attrs,
        private array $nodes,
    ) {}

    public function jsonSerialize(): mixed
    {
        return [
            'type'  => 'section',
            'attrs' => (object) $this->attrs,
            'nodes' => $this->nodes,
        ];
    }
}
