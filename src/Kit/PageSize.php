<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * The named values of the `size` attribute of a document or a section. A width and a height, such
 * as 100pt 200pt, is a string.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class PageSize
{
    public const A4 = 'a4';
    public const A3 = 'a3';
    public const A5 = 'a5';
    public const Letter = 'letter';
    public const Legal = 'legal';
}
