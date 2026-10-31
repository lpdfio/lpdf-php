<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * A named blend of colors. A background or a fill uses it as gradient:name, and the blend spans the
 * box it fills. Give from and to for two colors, or stop children for more.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class Gradient
{
    /**
     * @param list<GradientStop> $stops The colors of the gradient, for more than two: each at an
     * offset, in increasing order. Give these, or from and to.
     */
    public function __construct(
        /**
         * linear runs along a line across the box. radial runs from the center out to the farthest
         * corner.
         */
        public string $type,
        /**
         * The first color: hex, cmyk(), rgb(), hsl() or a color token, CMYK if to is and RGB if it
         * is not. Used with to, when there are no stop children. For a radial gradient it is the
         * center.
         */
        public ?string $from = null,
        /**
         * The last color: hex, cmyk(), rgb(), hsl() or a color token. Used with from, when there
         * are no stop children. For a radial gradient it is the edge.
         */
        public ?string $to = null,
        /**
         * The direction of a linear gradient, in degrees: 0 runs from the top to the bottom, 90
         * from the left to the right, 180 from the bottom to the top. Default 0. A radial gradient
         * has no direction.
         */
        public ?float $angle = null,
        public array $stops = [],
    ) {}
}
