<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * Code: the box of a fenced or indented block, and the run of inline code, which takes the font,
 * the color and the background. The language of a fence is ignored, since there is no highlighting
 * by language.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownCodeAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $fontSize = null,
        public ?string $color = null,
        public ?string $lineHeight = null,
        public ?string $padding = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $radius = null,
        public ?string $tabSize = null,
    ) {}
}
