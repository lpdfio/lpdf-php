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
        public ?string $bold = null,
        public ?string $color = null,
        /**
         * Makes the span a link: a URL, or #id to jump to the bookmark with that id. In an entry of
         * a table of contents, # alone jumps to that entry's bookmark.
         */
        public ?string $href = null,
        public ?string $underline = null,
        public ?string $strike = null,
    ) {}
}
