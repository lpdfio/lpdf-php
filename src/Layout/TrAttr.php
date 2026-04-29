<?php

declare(strict_types=1);

namespace Lpdf\Layout;

final readonly class TrAttr
{
    public function __construct(
        public ?string $background = null,
    ) {}
}
