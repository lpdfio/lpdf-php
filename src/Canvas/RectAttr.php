<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `rect` element in a layer.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class RectAttr
{
    public function __construct(
        public string $width,
        public string $height,
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $radius = null,
        public ?string $fill = null,
        public ?string $stroke = null,
        public ?string $strokeWidth = null,
        public ?string $strokeDash = null,
        /**
         * A hard-edged shadow: a copy of the shape, moved right and down by the first two lengths
         * (negative is left and up) and filled with the color, drawn under the shape. An opacity
         * from 0 to 1 after the color fades the shadow alone: "2pt 3pt #000000 0.2". There is no
         * blur. A shape with no fill shows the shadow only outside it.
         */
        public ?string $shadow = null,
    ) {}
}
