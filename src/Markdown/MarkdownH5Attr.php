<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A heading of level 5, written ##### in Markdown.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownH5Attr
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
    ) {}
}
