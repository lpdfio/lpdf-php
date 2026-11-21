<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * The values of the `justify` attribute of a column and a row: how the children are spread along
 * the box.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class BoxJustify
{
    public const Start = 'start';
    public const Center = 'center';
    public const End = 'end';
    public const Between = 'between';
}
