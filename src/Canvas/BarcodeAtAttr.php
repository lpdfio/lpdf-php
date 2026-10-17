<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * A barcode at a fixed place on the page. A QR code takes width or height. A code128 or ean13 needs
 * a width. x and y place its box: the top-left corner, or with anchor, an offset from that point of
 * the page, where the box's matching point goes.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class BarcodeAtAttr
{
    public function __construct(
        public string $type,
        public string $data,
        /**
         * Height of the whole barcode, with the digits of hrt inside it: they take the bottom 12pt
         * and the bars the rest. A QR code is square: it takes width or height, and the smaller
         * when both are set. Without a height, code128 has bars 40pt high, and 12pt more with hrt,
         * and ean13 is 60pt high.
         */
        public ?string $height = null,
        public ?string $ec = null,
        public ?string $hrt = null,
        public ?string $color = null,
        public ?string $background = null,
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $width = null,
    ) {}
}
