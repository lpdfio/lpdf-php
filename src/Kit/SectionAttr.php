<?php

declare(strict_types=1);

namespace Lpdf\Kit;

final readonly class SectionAttr
{
    public function __construct(
        public ?string      $size        = null,
        public ?string $orientation = null,
        public ?string      $background  = null,
        public ?string      $title       = null,
        public ?string      $debug       = null,
        public ?string      $type        = null,
        public ?string      $tocIndentStep = null,
    ) {}
}
