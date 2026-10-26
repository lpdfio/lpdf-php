<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A markdown inside a section, where a layout would go: it holds only Markdown, in its styles and its content.
 *
 * @internal Use L::markdown() to construct.
 */
final readonly class MarkdownNode implements \JsonSerializable
{
    /**
     * @param array<string,string>                         $attrs
     * @param array<MarkdownStylesNode|MarkdownContentNode> $nodes
     */
    public function __construct(
        private array $attrs,
        private array $nodes,
    ) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'markdown', 'attrs' => (object) $this->attrs, 'nodes' => $this->nodes];
    }
}
