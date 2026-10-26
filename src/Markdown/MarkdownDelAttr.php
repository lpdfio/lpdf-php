<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * Struck-through text, written ~~text~~ in Markdown.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownDelAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $color = null,
        public ?string $strike = null,
    ) {}
}
