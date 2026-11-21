<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * The values of the `anchor` attribute of a layer shape, a text-at, an img-at and a barcode-at: the
 * nine reference points of a page and of a box.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class Anchor
{
    public const TopLeft = 'top-left';
    public const TopCenter = 'top-center';
    public const TopRight = 'top-right';
    public const CenterLeft = 'center-left';
    public const Center = 'center';
    public const CenterRight = 'center-right';
    public const BottomLeft = 'bottom-left';
    public const BottomCenter = 'bottom-center';
    public const BottomRight = 'bottom-right';
}
