<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Places children top to bottom, each at the full width, with gap between them. Can split across
 * pages between children.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class StackAttr
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
        /**
         * Where the box falls in the page flow. no: never split the box, and move it whole to the
         * next page when it does not fit. break-before: start it on a new page. break-after: start
         * the next sibling on a new page. keep-next: keep it on one page with the sibling that
         * follows, moving both to the next page if that sibling would not fit. break-before,
         * break-after and keep-next take effect on the children of layout; on a box inside another
         * box they are not applied. no applies at any depth.
         */
        public ?string $paginate = null,
        public ?string $debug = null,
        public ?string $align = null,
        public ?string $justify = null,
        public ?string $width = null,
    ) {}
}
