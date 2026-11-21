<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * What a text is to a reader, beyond a paragraph: a heading of level 1 to 6.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class TextRole
{
    public const H1 = 'h1';
    public const H2 = 'h2';
    public const H3 = 'h3';
    public const H4 = 'h4';
    public const H5 = 'h5';
    public const H6 = 'h6';
}
