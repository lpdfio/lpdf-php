<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * The rules that say how the parts of a markdown look, with a file of them in `src` when it has one.
 *
 * @internal Use LMarkdown::styles() to construct.
 */
final readonly class MarkdownStylesNode implements \JsonSerializable
{
    /**
     * @param array<string,string> $attrs
     * @param MarkdownRuleNode[]   $nodes
     */
    public function __construct(
        private array $attrs,
        private array $nodes,
    ) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'markdown-styles', 'attrs' => (object) $this->attrs, 'nodes' => $this->nodes];
    }
}
