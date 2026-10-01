<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * The values of the `core` attribute of a font: the built-in PDF fonts, which need no file.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class BuiltinFont
{
    public const string Courier = 'Courier';
    public const string CourierBold = 'Courier-Bold';
    public const string CourierOblique = 'Courier-Oblique';
    public const string CourierBoldOblique = 'Courier-BoldOblique';
    public const string Helvetica = 'Helvetica';
    public const string HelveticaBold = 'Helvetica-Bold';
    public const string HelveticaOblique = 'Helvetica-Oblique';
    public const string HelveticaBoldOblique = 'Helvetica-BoldOblique';
    public const string TimesRoman = 'Times-Roman';
    public const string TimesBold = 'Times-Bold';
    public const string TimesItalic = 'Times-Italic';
    public const string TimesBoldItalic = 'Times-BoldItalic';
    public const string Symbol = 'Symbol';
    public const string ZapfDingbats = 'ZapfDingbats';
}
