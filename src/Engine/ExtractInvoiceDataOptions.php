<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/** The options of `LCrossIndustryInvoice::extractInvoiceData`. */
final readonly class ExtractInvoiceDataOptions
{
    public function __construct(
        /** The map, an XML document: without it, Lpdf's default map. A map given takes over whole. */
        public ?string $invoiceMap = null,
        /** How the values of each type are written. */
        public ?InvoiceFormat $format = null,
    ) {}
}
