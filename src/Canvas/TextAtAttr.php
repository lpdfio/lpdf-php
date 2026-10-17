<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * Text at a fixed place on the page. It is the text of the layout, with the same defaults: the
 * document's font or Helvetica, 11pt and the text colour. It is one line unless told otherwise;
 * give width to cut it off at a width or to wrap it with max-lines. x and y place its box: the
 * top-left corner, or with anchor, an offset from that point of the page, where the box's matching
 * point goes. {page} and {pages} in it count within the section, and a text that comes from data
 * belongs in the layout, since a longer text loses its end here without a warning.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class TextAtAttr
{
    public function __construct(
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
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
        /**
         * Width of the box. Without it the text is one line of the width its characters need, and
         * align has no effect; a long text runs on past the edge of the page, which cuts it off,
         * but stays in the PDF. With it, a line past the width is cut off at it, or ends in an
         * ellipsis with ellipsis, and max-lines above 1 wraps the text within it.
         */
        public ?string $width = null,
        /**
         * The most lines drawn, 1 by default. Above 1 it needs a width to wrap at, and wraps the
         * text within it; only whole lines are drawn, and the rest of the text is dropped.
         */
        public ?string $maxLines = null,
        /**
         * End the last line drawn in an ellipsis when the text does not fit its width and
         * max-lines. Off by default: the line is cut off at the width.
         */
        public ?string $ellipsis = null,
        /** The distance between lines, as a factor of the font size. Default 1.2. */
        public ?string $lineHeight = null,
    ) {}
}
