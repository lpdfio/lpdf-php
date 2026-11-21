<?php

declare(strict_types=1);

namespace Lpdf\Shared;

/**
 * The values of the `type` attribute of a barcode: the kind of code.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class BarcodeType
{
    public const Qr = 'qr';
    public const Code128 = 'code128';
    public const Ean13 = 'ean13';
    public const Code39 = 'code39';
    public const Ean8 = 'ean8';
    public const Upca = 'upca';
    public const Datamatrix = 'datamatrix';
    public const Pdf417 = 'pdf417';
    public const Gs1128 = 'gs1-128';
    public const Gs1Datamatrix = 'gs1-datamatrix';
}
