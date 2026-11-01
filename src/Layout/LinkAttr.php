<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Attributes of the `link` element.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class LinkAttr
{
    public function __construct(
        /**
         * Where the link goes: a URL, or #id to jump to the bookmark with that id. In an entry of a
         * table of contents, # alone jumps to that entry's bookmark.
         */
        public string $href,
        public ?string $gap = null,
        /**
         * Width of the element. A length fixes it. fill, in a row, takes the width its line leaves
         * after the other children, shared equally if several on the line use fill, and never moves
         * to the next line; in a column it takes the full width.
         */
        public ?string $width = null,
        public ?string $height = null,
        public ?bool $debug = null,
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
