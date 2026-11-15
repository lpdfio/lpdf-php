<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * What a `PdfEngine::merge` does beyond its defaults. Each option is off by default.
 */
final readonly class MergeOptions
{
    public function __construct(
        /**
         * Merge a file that is signed. A signature covers the bytes of its own file, so it is lost in the merged file,
         * and what it looked like still prints. Without it, a signed file is an error that names the file.
         */
        public bool $allowSigned = false,
        /**
         * Merge a file whose owner password restricts printing or copying, which opens without a password. Without it,
         * such a file is an error that names the file.
         */
        public bool $allowRestricted = false,
        /**
         * Add a blank side after a document that has an odd number of pages, the size of its last page, so that each
         * document starts on a front side when the result is printed on both sides of the sheet. None is added after
         * the last document.
         */
        public bool $doubleSided = false,
    ) {}
}
