<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A cell of a table's other rows.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownTdAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $fontSize = null,
        public ?bool $bold = null,
        public ?string $color = null,
        public ?string $align = null,
        public ?string $padding = null,
        public ?string $background = null,
        public ?string $border = null,
    ) {}
}
