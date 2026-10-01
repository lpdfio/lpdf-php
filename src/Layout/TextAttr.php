<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A block of wrapping text. Use span children to style parts of it. Splits across pages between
 * lines.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class TextAttr
{
    public function __construct(
        public ?string $fontSize = null,
        public ?string $font = null,
        /**
         * Use the bold face of the font. It applies to the 14 built-in fonts: Helvetica becomes
         * Helvetica-Bold, Times-Roman becomes Times-Bold, Courier becomes Courier-Bold, and the
         * oblique and italic faces their bold forms. A custom font has no bold face to pick, so
         * name one in font.
         */
        public ?string $bold = null,
        public ?string $color = null,
        public ?string $align = null,
        public ?string $width = null,
        public ?string $debug = null,
    ) {}
}
