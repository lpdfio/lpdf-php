<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * The values of the `type` attribute of a field.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class FieldType
{
    public const string Text = 'text';
    public const string Checkbox = 'checkbox';
    public const string Dropdown = 'dropdown';
    public const string Radio = 'radio';
    public const string Button = 'button';
}
