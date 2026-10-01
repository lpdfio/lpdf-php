<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Attributes of the `divider` element.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class DividerAttr
{
    public function __construct(
        public ?string $direction = null,
        public ?string $color = null,
        public ?string $thickness = null,
        public ?string $debug = null,
    ) {}
}
