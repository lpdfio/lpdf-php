<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `circle` element in a layer.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class CircleAttr
{
    public function __construct(
        public string $r,
        /**
         * Where the circle's box goes, the square of side 2r around it: its left edge, or with
         * anchor, an offset from that point of the page, where the box's matching point goes. The
         * centre is r further in.
         */
        public ?string $x = null,
        /**
         * Where the circle's box goes, the square of side 2r around it: its top edge, or with
         * anchor, an offset from that point of the page, where the box's matching point goes. The
         * centre is r further in.
         */
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $fill = null,
        public ?string $stroke = null,
        public ?string $strokeWidth = null,
        public ?string $strokeDash = null,
    ) {}
}
