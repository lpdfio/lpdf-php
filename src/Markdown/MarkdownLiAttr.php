<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * An item of a bullet or a numbered list. indent is the width of the marker's column and bullet the
 * shape of a bullet list's marker, as on a list; gap goes between the blocks of an item.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownLiAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $fontSize = null,
        public ?string $color = null,
        public ?string $gap = null,
        public ?string $padding = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $indent = null,
        public ?string $bullet = null,
    ) {}
}
