<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Attributes of the `barcode` element.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class BarcodeAttr
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
        /**
         * Width of the element. A length fixes it. fill, in a row, takes the width its line leaves
         * after the other children, shared equally if several on the line use fill, and never moves
         * to the next line; in a column it takes the full width.
         */
        public ?string $width = null,
        public ?string $paginate = null,
        public ?string $debug = null,
    ) {}
}
