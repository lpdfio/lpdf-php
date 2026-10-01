<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Groups canvas shapes and sets what applies to all of them: which pages they appear on, opacity,
 * transform and clip. Layers cannot be nested.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class LayerAttr
{
    public function __construct(
        public ?string $page = null,
        public ?string $opacity = null,
        public ?string $transform = null,
        public ?string $clip = null,
    ) {}
}
