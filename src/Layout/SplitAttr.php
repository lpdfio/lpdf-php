<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Two children side by side; any further children are ignored. By default each keeps its own width,
 * the first at the left edge and the second at the right. With equal set to true they take half the
 * width each. Never splits across pages.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class SplitAttr
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
         * false (the default): each child keeps its own width, the first at the left edge and the
         * second at the right edge. true: the two children share the width in equal halves.
         */
        public ?string $equal = null,
        public ?string $width = null,
    ) {}
}
