<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `rect` element on the canvas.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class RectAttr
{
    public function __construct(
        public string $w,
        public string $h,
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $radius = null,
        public ?string $fill = null,
        public ?string $stroke = null,
        public ?string $strokeWidth = null,
        public ?string $strokeDash = null,
        public ?string $opacity = null,
    ) {}
}
