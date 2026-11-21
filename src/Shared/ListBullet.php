<?php

declare(strict_types=1);

namespace Lpdf\Shared;

/**
 * The values of the `bullet` attribute of a list and a li: the shape that marks the items of a
 * bullet list.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class ListBullet
{
    public const Disc = 'disc';
    public const Circle = 'circle';
    public const Square = 'square';
    public const Dash = 'dash';
}
