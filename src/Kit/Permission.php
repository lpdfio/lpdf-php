<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * The words of the `allow` attribute of the output, separated by spaces: what a reader of an
 * encrypted PDF may do.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class Permission
{
    public const All = 'all';
    public const Print = 'print';
    public const Modify = 'modify';
    public const Copy = 'copy';
    public const Annotate = 'annotate';
    public const FillForms = 'fill-forms';
    public const Accessibility = 'accessibility';
    public const Assemble = 'assemble';
}
