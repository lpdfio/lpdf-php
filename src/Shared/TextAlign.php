<?php

declare(strict_types=1);

namespace Lpdf\Shared;

/**
 * The values of the `align` attribute of a text, a text-at and the markdown rules: how the lines of
 * a text align.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class TextAlign
{
    public const Left = 'left';
    public const Center = 'center';
    public const Right = 'right';
    public const Justify = 'justify';
}
