<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Attributes of the `link` element.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class LinkAttr
{
    public function __construct(
        public string $href,
        public ?string $gap = null,
        public ?string $width = null,
        public ?string $height = null,
        public ?string $debug = null,
    ) {}
}
