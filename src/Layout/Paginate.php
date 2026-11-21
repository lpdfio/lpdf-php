<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * The values of the `paginate` attribute: where a box falls in the page flow.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class Paginate
{
    public const No = 'no';
    public const BreakBefore = 'break-before';
    public const BreakAfter = 'break-after';
    public const KeepNext = 'keep-next';
}
