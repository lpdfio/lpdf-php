<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A run of code inside a text: its words in Courier on a light tint, the tint drawn behind them. It
 * is the inline twin of code, and like a span it is not broken inside, so it goes to the next line
 * whole, and a run longer than a whole line breaks at the last character that fits, so nothing runs
 * past the box. White space in it collapses as in the text around it.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class CodeSpanAttr
{
    public function __construct(
        public ?string $font = null,
        public ?string $color = null,
        /** The tint drawn behind the run. Default #f3f4f6. */
        public ?string $background = null,
    ) {}
}
