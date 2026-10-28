<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A table. Its columns are sized by their content: a column takes the width its cells need on one
 * line, the room that is left is shared in proportion, and a name that does not fit breaks at the
 * edge of its cell. stripe is the color of every other row, or none; head-border is the line under
 * the header row and row-border the line under each of the other rows, as thickness and color like
 * border, or none. The default styles draw a line in line-muted under the header and under each
 * row, with no stripe.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownTableAttr
{
    public function __construct(
        public ?string $border = null,
        public ?string $stripe = null,
        public ?string $headBorder = null,
        public ?string $rowBorder = null,
        public ?string $gap = null,
        public ?string $padding = null,
        public ?string $background = null,
    ) {}
}
