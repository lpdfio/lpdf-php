<?php

declare(strict_types=1);

namespace Lpdf;

use Lpdf\Layout\ChartCategoryAttr;
use Lpdf\Layout\ChartCategoryNode;
use Lpdf\Layout\ChartSeriesAttr;
use Lpdf\Layout\ChartSeriesNode;
use Lpdf\Layout\ChartValueNode;
use Lpdf\Shared\AttrsHelper;

/**
 * The parts of a chart: its series, its categories and their values. The chart itself is {@see L::chart()}.
 *
 * @example
 * ```php
 * L::chart(new ChartAttr(type: ChartType::Bar, height: '160pt', prefix: '$'), [
 *     LChart::series(new ChartSeriesAttr(label: 'Revenue')),
 *     LChart::category(new ChartCategoryAttr(label: 'Q1'), [LChart::value(NoAttr, [120000])]),
 * ])
 * ```
 */
final class LChart
{
    use AttrsHelper;

    /**
     * Build a series of a chart: its entry in the legend and the color of its bars, line or area. A pie, a donut and
     * a sparkline take none.
     */
    public static function series(?ChartSeriesAttr $attrs = null): ChartSeriesNode
    {
        return new ChartSeriesNode(self::optionsToAttrs($attrs));
    }

    /**
     * Build a category of a chart: a value for each series, in their order, and its label.
     *
     * @param ChartValueNode[] $nodes
     */
    public static function category(?ChartCategoryAttr $attrs = null, array $nodes = []): ChartCategoryNode
    {
        return new ChartCategoryNode(self::optionsToAttrs($attrs), $nodes);
    }

    /**
     * Build a value of a category: a number, written in full with no exponent and no locale, or a string that is
     * one, such as `'0.250'`, taken as written so its label keeps its decimals. No value, `[]`, draws no bar or
     * slice and breaks a line, an area or a sparkline; a stacked area counts it as zero. It has no attributes: pass
     * `NoAttr`.
     *
     * @param array<int|float|string> $nodes
     * @throws \InvalidArgumentException when a value is not an int, a finite float or a string.
     */
    public static function value(null $attrs = null, array $nodes = []): ChartValueNode
    {
        $texts = [];
        foreach ($nodes as $i => $value) {
            $texts[] = match (true) {
                is_int($value)    => (string) $value,
                is_float($value)  => self::plainNumber($value),
                is_string($value) => $value,
                default           => throw new \InvalidArgumentException(
                    "value() child at index $i must be an int, a float or a string, got " . get_debug_type($value),
                ),
            };
        }
        return new ChartValueNode($texts);
    }

    /**
     * A float in its shortest digits, with its exponent written out: `1.0e+21` is `1000000000000000000000`. The
     * digits are `json_encode`'s, with `serialize_precision` at -1 for the call, whatever the host sets it to.
     *
     * @throws \InvalidArgumentException when the float is not finite.
     */
    private static function plainNumber(float $value): string
    {
        if (!is_finite($value)) {
            throw new \InvalidArgumentException("a chart's value is a finite number, not $value");
        }
        $precision = ini_get('serialize_precision');
        ini_set('serialize_precision', '-1');
        try {
            $text = (string) json_encode($value);
        } finally {
            ini_set('serialize_precision', $precision === false ? '-1' : $precision);
        }
        if (!preg_match('/^(-?)(\d+)(?:\.(\d+))?e([+-]\d+)$/i', $text, $exponent)) {
            return $text === '-0' ? '0' : $text;
        }
        [, $sign, $whole] = $exponent;
        $digits = rtrim($whole . ($exponent[3] ?? ''), '0');
        $digits = $digits === '' ? '0' : $digits;
        $point = strlen($whole) + (int) $exponent[4];
        if ($point <= 0) {
            return $sign . '0.' . str_repeat('0', -$point) . $digits;
        }
        if ($point >= strlen($digits)) {
            return $sign . $digits . str_repeat('0', $point - strlen($digits));
        }
        return $sign . substr($digits, 0, $point) . '.' . substr($digits, $point);
    }
}
