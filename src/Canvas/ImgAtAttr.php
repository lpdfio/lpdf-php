<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * An image at a fixed place on the page. It needs width or height, and the other follows the
 * image's proportions. x and y place its box: the top-left corner, or with anchor, an offset from
 * that point of the page, where the box's matching point goes.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class ImgAtAttr
{
    public function __construct(
        public string $name,
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $width = null,
        public ?string $height = null,
    ) {}
}
