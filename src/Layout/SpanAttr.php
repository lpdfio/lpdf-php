<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Attributes of the `span` element.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class SpanAttr
{
    public function __construct(
        public ?string $font = null,
        public ?bool $bold = null,
        public ?string $color = null,
        /**
         * Makes the span a link: a URL, or #id to jump to the bookmark with that id. In an entry of
         * a table of contents, # alone jumps to that entry's bookmark.
         */
        public ?string $href = null,
        /**
         * Makes the span a cross-reference to the bookmark with this id: a link to it whose words
         * say where it is. In the span's words, {bookmark-page} prints the page the bookmark is on,
         * as the footer of that page prints it, and {bookmark-label} the bookmark's label, so
         * "{bookmark-label}, on page {bookmark-page}" shows "clause 4, on page 12". An id that no
         * bookmark has, another {…} token in the words, and href beside it are errors. It works in
         * a text, a footnote, a region, a text-at and a code. The document is laid out again until
         * each reference shows the page its bookmark lands on, and a reference keeps room for the
         * widest words it has shown, so a number that got narrower can leave a small gap after it.
         */
        public ?string $bookmarkRef = null,
        public ?bool $underline = null,
        public ?bool $strike = null,
    ) {}
}
