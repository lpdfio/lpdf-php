<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * One stamp for `PdfEngine::stamp`: a PNG, the pages it goes on, and the box it is drawn in on each. The box is placed
 * as a layer element is, and the image is fitted in it without distortion.
 */
final readonly class StampAttr
{
    public function __construct(
        /** The PNG's bytes. The same bytes, given to several stamps, are embedded in the PDF once. */
        public string  $image,
        /** The pages: a page scope, such as `3`, `1-2`, `last`, `each`, `odd` or `1,3,5-last`. A page the PDF doesn't have is an error. */
        public string  $page,
        /** The width of the box, in `pt`, `mm` or `in`. */
        public string  $width,
        /** The height of the box, in `pt`, `mm` or `in`. */
        public string  $height,
        /**
         * Where the box goes across, in `pt`, `mm` or `in`: from the page's left edge as a viewer shows it, or with
         * `anchor`, a signed offset from that point of the page. Required without an anchor.
         */
        public ?string $x = null,
        /**
         * Where the box goes down, in `pt`, `mm` or `in`: from the page's top edge as a viewer shows it, or with
         * `anchor`, a signed offset from that point of the page. Required without an anchor.
         */
        public ?string $y = null,
        /**
         * The point of the page the box is placed from, and the point of the box that goes there: `top-left`,
         * `top-center`, `top-right`, `center-left`, `center`, `center-right`, `bottom-left`, `bottom-center` or
         * `bottom-right`.
         */
        public ?string $anchor = null,
        /** Where the image sits across the box, in the room it leaves: `start`, `center`, the default, or `end`. */
        public ?string $align = null,
        /** Where the image sits down the box, in the room it leaves: `start`, `center`, the default, or `end`. */
        public ?string $justify = null,
        /**
         * How the image is sized in the box, keeping its proportions: `fit`, the default, shrinks an image larger than
         * the box and keeps a smaller one at its natural size, from the resolution the PNG records, or a pixel to a
         * point; `fill` takes the largest size that fits.
         */
        public ?string $scale = null,
        /** How many times its natural size `fit` may grow an image, never past the box: 1, the default, or more. */
        public ?float  $maxGrow = null,
    ) {}
}
