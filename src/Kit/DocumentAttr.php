<?php

declare(strict_types=1);

namespace Lpdf\Kit;

final readonly class DocumentAttr
{
    /**
     * @param list<FileAttr>|null $attachments The files the PDF carries inside it, as the `attachments` element of
     *                                         the XML does: a reader lists them in its attachments panel. Each file's
     *                                         bytes are read from its `src`, or loaded with `loadFile` under its
     *                                         `ref` or name.
     */
    public function __construct(
        public ?string         $size        = null,
        public ?string         $orientation = null,
        public ?string         $background  = null,
        public ?string         $font        = null,
        public ?string         $debug       = null,
        public ?DocumentAssets $assets      = null,
        public ?DocumentTokens $tokens      = null,
        public ?DocumentMeta   $meta        = null,
        public ?string         $footnoteCounter = null,
        public ?DocumentOutput $output      = null,
        public ?array          $attachments = null,
    ) {}
}
