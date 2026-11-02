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
    public const Text = 'text';
    public const Checkbox = 'checkbox';
    public const Dropdown = 'dropdown';
    public const Radio = 'radio';
    public const Button = 'button';
}
