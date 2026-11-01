<?php

declare(strict_types=1);

namespace Lpdf\Kit;

final readonly class SectionAttr
{
    /**
     * @param string|null $bleed How far this section's pages reach past their trim edge, such as `5mm`. Overrides the
     *                           document's; `0mm` takes it away.
     */
    public function __construct(
        public ?string      $size        = null,
        public ?string $orientation = null,
        public ?string      $background  = null,
        public ?string      $title       = null,
        public ?bool        $debug       = null,
        public ?string      $type        = null,
        public ?string      $tocIndentStep = null,
        public ?string      $footnoteCounter = null,
        public ?string      $bleed       = null,
    ) {}
}
