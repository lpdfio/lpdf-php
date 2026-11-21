<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * What a reader of an encrypted PDF may do without the owner password: all of what follows, or
 * print (at full quality), modify, copy, annotate, fill in forms, extract text for accessibility
 * tools, and assemble (insert, delete and rotate pages).
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
