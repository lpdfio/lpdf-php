<?php

declare(strict_types=1);

namespace Lpdf\Shared;

/**
 * The values of the `format` attribute of a list and the `page-format` attribute of a section: how
 * numbers are written.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class ListFormat
{
    public const Decimal = 'decimal';
    public const LowerAlpha = 'lower-alpha';
    public const UpperAlpha = 'upper-alpha';
    public const LowerRoman = 'lower-roman';
    public const UpperRoman = 'upper-roman';
}
