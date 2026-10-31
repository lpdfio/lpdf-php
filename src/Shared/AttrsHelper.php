<?php

declare(strict_types=1);

namespace Lpdf\Shared;

/** @internal Shared by Kit and Layout. */
trait AttrsHelper
{
    /**
     * Reflect over a readonly options object and convert its non-null properties to a kebab-case
     * attribute map, skipping named properties.
     *
     * Each value keeps the type the class gives it, as the schema types the attribute: a string, a
     * bool (`bold: true`), an int (`cols: 3`) or a float (`opacity: 0.5`). A BackedEnum is written as
     * its backing value. The engine refuses a value of the wrong type when it renders.
     *
     * @param  string[] $skip Property names to exclude (handled separately).
     * @return array<string,string|int|float|bool>
     */
    private static function optionsToAttrs(?object $options, array $skip = []): array
    {
        if ($options === null) {
            return [];
        }

        $attrs = [];
        foreach ((new \ReflectionClass($options))->getProperties() as $prop) {
            $name = $prop->getName();
            if (in_array($name, $skip, true)) {
                continue;
            }
            $value = $prop->getValue($options);
            if ($value instanceof \BackedEnum) {
                $attrs[self::camelToKebab($name)] = $value->value;
            } elseif (is_string($value) || is_bool($value) || is_int($value) || is_float($value)) {
                $attrs[self::camelToKebab($name)] = $value;
            }
        }

        return $attrs;
    }

    /** camelCase / PascalCase → kebab-case. */
    private static function camelToKebab(string $name): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '-$0', lcfirst($name)));
    }
}
