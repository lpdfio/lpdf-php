<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Attributes of the `img` element.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class ImgAttr
{
    public function __construct(
        public string $name,
        public ?string $height = null,
        public ?string $width = null,
        public ?string $font = null,
        public ?string $fontSize = null,
        public ?string $gap = null,
        public ?string $padding = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $radius = null,
        public ?string $paginate = null,
        public ?string $debug = null,
    ) {}
}
