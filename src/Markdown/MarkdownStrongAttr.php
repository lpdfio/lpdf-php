<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * Strong text, written **text** in Markdown. Bold in a built-in font; an embedded font names its
 * bold face in font.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownStrongAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $color = null,
        public ?bool $bold = null,
    ) {}
}
