<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Attributes of the `field` element.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class FieldAttr
{
    public function __construct(
        public string $type,
        public string $name,
        public ?string $value = null,
        public ?string $label = null,
        public ?string $options = null,
        public ?string $group = null,
        public ?string $checked = null,
        public ?string $required = null,
        public ?string $readonly = null,
        public ?string $maxLen = null,
        public ?string $actionUrl = null,
        public ?string $width = null,
        public ?string $height = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $debug = null,
    ) {}
}
