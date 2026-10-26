<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A rule of a markdown's styles: the element it is named after, with the attributes it sets.
 *
 * @internal Use LMarkdown::h1(), LMarkdown::quote() and the like to construct.
 */
final readonly class MarkdownRuleNode implements \JsonSerializable
{
    /** @param array<string,string> $attrs */
    public function __construct(
        private string $type,
        private array $attrs,
    ) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => $this->type, 'attrs' => (object) $this->attrs];
    }
}
