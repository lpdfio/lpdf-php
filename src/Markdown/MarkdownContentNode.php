<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * The Markdown itself, as strings that make one text.
 *
 * @internal Use LMarkdown::content() to construct.
 */
final readonly class MarkdownContentNode implements \JsonSerializable
{
    /** @param string[] $nodes */
    public function __construct(private array $nodes) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'markdown-content', 'attrs' => (object) [], 'nodes' => $this->nodes];
    }
}
