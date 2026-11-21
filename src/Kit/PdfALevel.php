<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * A level of PDF/A, the ISO 19005 standard for files kept for the long term: 2b keeps the look of
 * every page, 2u adds that all text maps to Unicode, so it can be searched and copied, and 2a adds
 * to 2u the tagged structure a screen reader reads. 3a, 3b and 3u are the same levels in part 3,
 * which also carries files of any kind, as a Factur-X or ZUGFeRD e-invoice carries its XML.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class PdfALevel
{
    public const Level2a = '2a';
    public const Level2b = '2b';
    public const Level2u = '2u';
    public const Level3a = '3a';
    public const Level3b = '3b';
    public const Level3u = '3u';
}
