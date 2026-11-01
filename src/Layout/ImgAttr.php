<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * An image. It is never stretched. With only a width or a height, the other follows the image's
 * proportions. With both, they are a maximum: the image takes the largest size that fits in them,
 * and its box becomes that size, which the parent's align places. To put images of different shapes
 * in slots of one size, wrap each in a frame of that size. With neither, the image has its natural
 * size.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class ImgAttr
{
    public function __construct(
        public string $name,
        /**
         * What the image shows, which a screen reader reads in a PDF/UA document; alt="" marks it
         * as decoration, which a reader skips. A PDF/UA document needs it on every image of the
         * layout. A document without pdfua ignores it.
         */
        public ?string $alt = null,
        public ?string $height = null,
        /**
         * Width of the element. A length fixes it. fill, in a row, takes the width its line leaves
         * after the other children, shared equally if several on the line use fill, and never moves
         * to the next line; in a column it takes the full width.
         */
        public ?string $width = null,
        public ?string $font = null,
        public ?string $fontSize = null,
        public ?string $gap = null,
        public ?string $padding = null,
        public ?string $background = null,
        public ?string $border = null,
        public ?string $radius = null,
        public ?string $paginate = null,
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
         * pdfa, pdfua or pdfx on the output, everything shows.
         */
        public ?string $ocg = null,
    ) {}
}
