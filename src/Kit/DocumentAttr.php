<?php

declare(strict_types=1);

namespace Lpdf\Kit;

final readonly class DocumentAttr
{
    public function __construct(
        public ?string         $size        = null,
        public ?string         $orientation = null,
        public ?string         $margin      = null,
        public ?string         $background  = null,
        public ?string         $font        = null,
        public ?string         $debug       = null,
        public ?DocumentAssets $assets      = null,
        public ?DocumentTokens $tokens      = null,
        public ?DocumentMeta   $meta        = null,
    ) {}
}
