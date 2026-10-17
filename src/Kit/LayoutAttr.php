<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * The flowing content of a section. Elements stack down the page, and a new page starts when one
 * runs out of room. Put headers and footers in a region, which sits outside the flow.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class LayoutAttr
{
    public function __construct(
        /**
         * The space between the page edges and the flowing content and its regions: one value for
         * all four sides, or two to four in the order top, right, bottom, left. Defaults to 36pt.
         * Layers ignore it and measure from the page edges.
         */
        public ?string $margin = null,
    ) {}
}
