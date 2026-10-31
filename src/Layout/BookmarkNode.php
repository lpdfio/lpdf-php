<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * An entry of the PDF's outline and a target for href="#id" links. It marks the node that follows it; directly
 * in a section's nodes, it marks the section's first page.
 *
 * @internal Use L::bookmark() to construct.
 */
final readonly class BookmarkNode extends Node
{
    /**
     * @param array<string,string|int|float|bool> $attrs  Must include 'label'.
     */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return [
            'type'  => 'bookmark',
            'attrs' => (object) $this->attrs,
        ];
    }
}
