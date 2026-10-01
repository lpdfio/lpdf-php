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
        public ?string $size = null,
        public ?string $width = null,
        public ?string $height = null,
        public ?string $ec = null,
        public ?string $hrt = null,
        public ?string $color = null,
        public ?string $background = null,
        public ?string $debug = null,
    ) {}
}
