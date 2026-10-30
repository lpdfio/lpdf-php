<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * Markdown as the content of a section, where a layout would go. It holds only Markdown: optional
 * markdown-styles, which say how each part of it looks, and one markdown-content, which holds the
 * text. The page, a header, a footer or a cover are not Markdown, so they stay in the XML: layers
 * paint a header and a footer in the margin, and a cover or a table of contents is a section of its
 * own. CommonMark with tables, strikethrough and footnotes, and a $ is text. Raw HTML is for an
 * HTML output, which a PDF is not, so a tag is left out and the words in it are kept: a block such
 * as a p or a div puts the words around it on lines of their own. Two tags are read: br breaks a
 * line, and img shows an image, at its width and height in pixels when it has them, which
 * Markdown's own image has no way to say. An image at a URL, in Markdown or in an img, is left out,
 * since an image must be an asset. A heading is a bookmark, so the table of contents and the
 * outline of the viewer fill themselves.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownAttr
{
    public function __construct(
        /**
         * The space between the page edges and the Markdown: one value for all four sides, or two
         * to four in the order top, right, bottom, left. Defaults to 36pt. A layer draws a header
         * or a footer in it.
         */
        public ?string $margin = null,
        /** The font of the Markdown, which a rule can change. Defaults to the document's. */
        public ?string $font = null,
        /** The size of the text of the Markdown, which a rule can change. Defaults to 11pt. */
        public ?string $fontSize = null,
        /**
         * The color of the text of the Markdown, which a rule can change. Defaults to the text
         * token.
         */
        public ?string $color = null,
        /**
         * The space between the blocks: headings, paragraphs, lists, code, quotes, tables and
         * images. Default none.
         */
        public ?string $gap = null,
        /**
         * How many levels of headings become bookmarks, for the outline of the viewer and the table
         * of contents: n keeps the levels 1 to n, 0 none. Default 6.
         */
        public ?string $bookmarks = null,
        public ?string $debug = null,
    ) {}
}
