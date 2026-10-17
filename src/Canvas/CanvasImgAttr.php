<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `img` element in a layer.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class CanvasImgAttr
{
    public function __construct(
        public string $name,
        public string $width,
        public string $height,
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
    ) {}
}
