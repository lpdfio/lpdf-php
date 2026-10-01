<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `line` element on the canvas.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class LineAttr
{
    public function __construct(
        public string $x1,
        public string $y1,
        public string $x2,
        public string $y2,
        public ?string $stroke = null,
        public ?string $strokeWidth = null,
        public ?string $strokeDash = null,
        public ?string $lineCap = null,
    ) {}
}
