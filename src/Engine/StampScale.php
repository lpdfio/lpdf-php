<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * The values of the `scale` option of a stamp: how its image is sized in its box, keeping its
 * proportions.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class StampScale
{
    public const Fit = 'fit';
    public const Fill = 'fill';
}
