<?php

declare(strict_types=1);

namespace Lpdf\Engine;

final readonly class EngineOptions
{
    public function __construct(
        public ?string $wasmBinary = null,   // path to .wasm file
        public ?string $wasmRunner = null,   // runner executable name/path
        /** The seconds a render may run before it stops with an EngineException: 5 when left out, no limit at 0. */
        public ?int    $timeout    = null,
    ) {}
}
