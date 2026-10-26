<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * Emphasis, written *text* in Markdown. Italic in a built-in font; an embedded font names its
 * italic face in font.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownEmAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $color = null,
    ) {}
}
