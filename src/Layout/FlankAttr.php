<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * One row where the children keep their own width except one, which fills the rest. By default the
 * last child fills; set end to true and the first fills. Never splits across pages.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class FlankAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $fontSize = null,
        public ?string $gap = null,
        /**
         * Space between the edge of the box and its content, written like CSS: one value for all
         * sides, two for top-bottom and left-right, three for top, left-right and bottom, four for
         * top, right, bottom and left.
         */
        public ?string $padding = null,
        /**
         * Height of the box. Leave it out to size to the content. A length fixes it; fill takes
         * what is left after its siblings (shared equally if several use fill); full takes all the
         * height available. Any of these stops the box splitting across pages.
         */
        public ?string $height = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $radius = null,
        public ?string $debug = null,
        public ?string $align = null,
        /**
         * false (the default): every child but the last keeps its own width at the left, and the
         * last fills the rest. true: the first child fills, and the others keep their own width at
         * the right.
         */
        public ?string $end = null,
        public ?string $width = null,
    ) {}
}
