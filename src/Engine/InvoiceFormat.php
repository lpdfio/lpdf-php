<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * How the values of each type are written on the page, for `LCrossIndustryInvoice`: each function takes the value in
 * its normal form and the full name of its entry in the map, without the prefix or an item number (`seller.country`,
 * `lines.unit`), and returns the text to bind. A type without a function is bound as the invoice has it.
 */
final readonly class InvoiceFormat
{
    public function __construct(
        /** A date, as `2026-10-31`, or `2026-10` and `2026-W44` for a month and a week. */
        public ?\Closure $date = null,
        /** An amount, as the invoice writes it: `1250.10`. */
        public ?\Closure $amount = null,
        /** A code, as the invoice writes it: `H87`, `S`, `381`. Lpdf carries no words for codes. */
        public ?\Closure $code = null,
    ) {}
}
