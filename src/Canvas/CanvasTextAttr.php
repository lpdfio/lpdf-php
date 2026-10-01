<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Text at an exact position on the page: either x and y, or an anchor with optional offsets.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class CanvasTextAttr
{
    public function __construct(
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $font = null,
        public ?string $fontSize = null,
        public ?string $color = null,
        public ?string $align = null,
        public ?string $w = null,
        public ?string $lineHeight = null,
        public ?string $opacity = null,
    ) {}
}
