<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `path` element on the canvas.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class PathAttr
{
    public function __construct(
        public string $d,
        public ?string $fill = null,
        public ?string $stroke = null,
        public ?string $fillRule = null,
        public ?string $strokeWidth = null,
        public ?string $strokeDash = null,
        public ?string $lineCap = null,
        public ?string $opacity = null,
    ) {}
}
