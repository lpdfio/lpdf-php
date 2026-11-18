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
        /**
         * The runner that reads the invoice, as an engine's options set it: which runner, the native library's and
         * wasmtime's paths, the timeout and the memory cap. For a host whose runner isn't the default one, as when
         * wasmtime is not on the PATH. Without it, the default runner, under the default timeout and memory cap.
         */
        public ?EngineOptions $engineOptions = null,
    ) {}
}
