<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Attributes of the `ellipse` element in a layer.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class EllipseAttr
{
    public function __construct(
        public string $rx,
        public string $ry,
        /**
         * Where the ellipse's box goes, 2rx wide and 2ry high around it: its left edge, or with
         * anchor, an offset from that point of the page, where the box's matching point goes. The
         * centre is rx further in.
         */
        public ?string $x = null,
        /**
         * Where the ellipse's box goes, 2rx wide and 2ry high around it: its top edge, or with
         * anchor, an offset from that point of the page, where the box's matching point goes. The
         * centre is ry further in.
         */
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $fill = null,
        public ?string $stroke = null,
        public ?string $strokeWidth = null,
        public ?string $strokeDash = null,
    ) {}
}
