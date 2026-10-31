<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * One color of a gradient, at an offset along it.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class GradientStop
{
    public function __construct(
        /**
         * Where the color is reached along the gradient, from 0% (the start) to 100% (the end).
         * Stops go in increasing order.
         */
        public string $offset,
        /**
         * The color at the stop: hex, cmyk(), rgb(), hsl() or a color token. The stops of a
         * gradient are all CMYK or all RGB.
         */
        public string $color,
    ) {}
}
