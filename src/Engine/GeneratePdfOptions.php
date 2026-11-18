<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/** The options of `LCrossIndustryInvoice::generatePdf`. */
final readonly class GeneratePdfOptions
{
    public function __construct(
        /** The invoice: its XML, which the values are read from and which the PDF carries byte for byte. */
        public string $invoiceXml,
        /** The ISO 8601 creation date of the file, which PDF/A requires. */
        public string $createdOn,
        /**
         * The map, an XML document: without it, Lpdf's default map. A map given takes over whole. A document built in
         * code has no bindings and needs none.
         */
        public ?string $invoiceMap = null,
        /** How the values of each type are written. */
        public ?InvoiceFormat $format = null,
        /** The values the XML lacks, which a template binds without a prefix, as `note`. */
        public mixed $data = null,
    ) {}
}
