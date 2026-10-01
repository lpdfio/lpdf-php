<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `img` element on the canvas.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class CanvasImgAttr
{
    public function __construct(
        public string $name,
        public string $w,
        public string $h,
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
    ) {}
}
