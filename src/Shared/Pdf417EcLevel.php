<?php

declare(strict_types=1);

namespace Lpdf\Shared;

/**
 * The values of the `ec` attribute of a pdf417: its error correction level, from 0 to 8.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class Pdf417EcLevel
{
    public const Level0 = '0';
    public const Level1 = '1';
    public const Level2 = '2';
    public const Level3 = '3';
    public const Level4 = '4';
    public const Level5 = '5';
    public const Level6 = '6';
    public const Level7 = '7';
    public const Level8 = '8';
}
