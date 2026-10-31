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
    public const string All = 'all';
    public const string Print = 'print';
    public const string Modify = 'modify';
    public const string Copy = 'copy';
    public const string Annotate = 'annotate';
    public const string FillForms = 'fill-forms';
    public const string Accessibility = 'accessibility';
    public const string Assemble = 'assemble';
}
