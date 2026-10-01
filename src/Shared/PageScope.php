<?php

declare(strict_types=1);

namespace Lpdf\Shared;

/**
 * The named values of the `page` attribute of a layer or a region. A range such as 2-4 or 1,3-5 is
 * a string.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class PageScope
{
    public const string Each = 'each';
    public const string First = 'first';
    public const string Last = 'last';
    public const string Odd = 'odd';
    public const string Even = 'even';
}
