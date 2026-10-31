<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * A barcode at a fixed place on the page. It needs width or height, and the other follows the
 * barcode's proportions. With both, they are a maximum: the barcode takes the largest size that
 * fits in them, and its box becomes that size, so an anchor places the barcode itself. x and y
 * place its box: the top-left corner, or with anchor, an offset from that point of the page, where
 * the box's matching point goes.
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
         * and the bars the rest. A barcode is never stretched. With only a height, the width
         * follows the barcode's proportions. With both width and height, they are a maximum: the
         * barcode takes the largest size that fits in them, and its box becomes that size. A QR
         * code is square, so it takes the smaller of the two. Without a size, a code128 is as wide
         * as its bars at 1pt per module and its bars are 40pt high, and an ean13 is the standard
         * symbol, 0.33mm per module and bars 22.85mm high. The digits of hrt add 12pt to either.
         */
        public ?string $height = null,
        public ?string $ec = null,
        public ?bool $hrt = null,
        public ?string $color = null,
        public ?string $background = null,
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $width = null,
    ) {}
}
