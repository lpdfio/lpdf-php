<?php

declare(strict_types=1);

namespace Lpdf\Shared;

/**
 * The values of the `ocg` attribute of an element, a layer or a ruling: an optional content group,
 * a PDF layer that shows on screen only or in print only.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class Ocg
{
    public const Print = 'print';
    public const Screen = 'screen';
}
