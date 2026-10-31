<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A paragraph, and the words of a list item, a quote and a footnote.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownPAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $fontSize = null,
        public ?bool $bold = null,
        public ?string $color = null,
        public ?string $align = null,
        public string|float|null $lineHeight = null,
        public ?string $padding = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $radius = null,
    ) {}
}
