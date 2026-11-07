<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * A thematic break, written --- in Markdown: a divider. color is the color of the line and
 * thickness its thickness, a length or a name from the border row of the tokens.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownHrAttr
{
    public function __construct(
        public ?string $color = null,
        public ?string $thickness = null,
    ) {}
}
