<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Attributes of the `tr` element.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class TrAttr
{
    public function __construct(
        /**
         * with-next keeps the row on the same page as the row after it. A run of rows that each say
         * with-next, and the row that ends it, move to the next page together when a page break
         * would fall inside them. A run taller than a page breaks where its rows fit, as if it had
         * no keep. Rows are never split, and a header is never left alone at the bottom of a page,
         * with or without this.
         */
        public ?string $keep = null,
        public ?string $background = null,
        /**
         * Puts the element, and everything in it, in an optional content group, which the viewer
         * shows or hides. print: printed, and not shown on screen. screen: shown on screen, and not
         * printed. The element keeps its room either way, so nothing moves. An element with a group
         * inside an element with another shows only where both show. Acrobat, Chrome and Firefox
         * switch the groups; Preview and Safari show everything, on screen and in print. Hidden
         * content is still in the file, where it can be selected and copied: it is not a way to
         * keep anything secret. A link in print content is drawn and does nothing, and a form field
         * can't be in print content: use a frame with a border for a box filled in by hand. With
         * pdfa or pdfua on the output, everything shows. With pdfx, the file is the print version:
         * screen content stays in it, off, and a reader can turn it on in a layers panel.
         */
        public ?string $ocg = null,
    ) {}
}
