<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Draws shapes at exact positions on the page, independent of the flowing layout: watermarks,
 * stamps, page furniture. Coordinates start at the top left corner of the whole page, not inside
 * the layout's margin, and y grows downward. A layer before the section's layout paints under it,
 * and one after paints over it. It sets what applies to all of its shapes: which pages they appear
 * on, opacity, transform and clip. Layers cannot be nested.
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
