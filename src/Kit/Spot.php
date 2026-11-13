<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * A spot color: an ink of its own, such as a brand color, a varnish or a die-cut line, printed from
 * a plate of its own. Anything that takes a color takes its name, solid, or as a tint, a percentage
 * of the ink after a space: brand 30%.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class Spot
{
    public function __construct(
        /**
         * The name of the ink as the printer knows it, which names its plate, such as PANTONE 1665
         * C. Each spot color has an ink of its own. Not All or None, which PDF keeps for every
         * plate and no plate, and not Cyan, Magenta, Yellow or Black, the process inks that cmyk()
         * prints with.
         */
        public string $ink,
        /**
         * The color that stands in for the ink where it is not printed as a plate of its own, on a
         * screen or a proof: hex, cmyk(), rgb() or hsl(), not a color token.
         */
        public string $value,
        /**
         * Whether the ink prints on top of the inks under it, as a varnish or a die-cut line does,
         * instead of leaving them out where it sits. A screen doesn't show it, a press and an
         * overprint preview do. Defaults to false.
         */
        public ?bool $overprint = null,
    ) {}
}
