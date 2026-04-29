<?php

declare(strict_types=1);

namespace Lpdf\Engine;

final readonly class RenderOptions
{
    public function __construct(
        public ?string     $createdOn = null,  // ISO 8601 for PDF /CreationDate
        public mixed       $data      = null,  // data object for data-* bindings (XML only)
    ) {}
}
