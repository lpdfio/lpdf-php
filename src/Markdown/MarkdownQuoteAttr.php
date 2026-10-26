<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A block quote, written > in Markdown: a bar beside its content. font, font-size, color,
 * line-height and align set the words; padding, background, border and radius the box around the
 * bar and the words; bar-color and bar-width the bar, a strip along the left of the box: bar-width
 * is a length or a name from the space row of the tokens.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownQuoteAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $fontSize = null,
        public ?string $bold = null,
        public ?string $color = null,
        public ?string $align = null,
        public ?string $lineHeight = null,
        public ?string $padding = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $radius = null,
        public ?string $barColor = null,
        public ?string $barWidth = null,
    ) {}
}
