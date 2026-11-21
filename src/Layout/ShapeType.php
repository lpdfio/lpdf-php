<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * The values of the `type` attribute of a shape.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class ShapeType
{
    public const Rect = 'rect';
    public const Circle = 'circle';
    public const Ellipse = 'ellipse';
}
