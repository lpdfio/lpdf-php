<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A table. The columns share the width equally.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownTableAttr
{
    public function __construct(
        public ?string $border = null,
        public ?string $stripe = null,
        public ?string $gap = null,
        public ?string $padding = null,
        public ?string $background = null,
    ) {}
}
