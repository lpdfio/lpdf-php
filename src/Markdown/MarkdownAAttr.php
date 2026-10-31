<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A link, written [text](url) in Markdown.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownAAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $color = null,
        public ?bool $bold = null,
        public ?bool $underline = null,
    ) {}
}
