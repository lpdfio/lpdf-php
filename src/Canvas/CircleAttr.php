<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `circle` element on the canvas.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class CircleAttr
{
    public function __construct(
        public string $r,
        public ?string $cx = null,
        public ?string $cy = null,
        public ?string $anchor = null,
        public ?string $fill = null,
        public ?string $stroke = null,
        public ?string $strokeWidth = null,
        public ?string $strokeDash = null,
        public ?string $opacity = null,
    ) {}
}
