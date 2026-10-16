<?php

declare(strict_types=1);

namespace Lpdf\Kit;

use Lpdf\Canvas\LayerNode;
use Lpdf\Layout\BookmarkNode;

/**
 * A section node. Its children are an optional {@see BookmarkNode} that marks the
 * section's first page, then {@see LayerNode}s, then at most one {@see SectionLayout},
 * then {@see LayerNode}s, in paint order: the layers before the layout paint under
 * it, and the layers after it paint over it.
 *
 * @internal Use Kit::section() to construct.
 */
final readonly class SectionNode implements \JsonSerializable
{
    /**
     * @param array<string,string>                          $attrs
     * @param array<BookmarkNode|LayerNode|SectionLayout> $nodes  In paint order.
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
