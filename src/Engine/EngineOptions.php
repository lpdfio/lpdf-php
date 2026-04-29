<?php

declare(strict_types=1);

namespace Lpdf\Engine;

final readonly class EngineOptions
{
    public function __construct(
        public ?string $wasmBinary = null,   // path to .wasm file
        public ?string $wasmRunner = null,   // runner executable name/path
        public ?int    $timeout    = null,   // WASI process timeout in seconds (default: 30)
    ) {}
}
