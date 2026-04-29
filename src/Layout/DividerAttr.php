<?php

declare(strict_types=1);

namespace Lpdf\Layout;

final readonly class DividerAttr
{
    public function __construct(
        public ?string $color     = null,
        public ?string $thickness = null,
        public ?string $direction = null,
    ) {}
}
