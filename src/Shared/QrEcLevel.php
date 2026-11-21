<?php

declare(strict_types=1);

namespace Lpdf\Shared;

/**
 * The values of the `ec` attribute of a qr: its error correction, from 7% to 30% of the code.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class QrEcLevel
{
    public const L = 'L';
    public const M = 'M';
    public const Q = 'Q';
    public const H = 'H';
}
