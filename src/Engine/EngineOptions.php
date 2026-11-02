<?php

declare(strict_types=1);

namespace Lpdf\Engine;

final readonly class EngineOptions
{
    /** The most `maxMemoryMb` may be: 32-bit WebAssembly addresses 4 GB at most. */
    private const MOST_MEMORY_MB = 4096;

    /**
     * @throws \InvalidArgumentException When `maxMemoryMb` is below 1 or above 4096.
     */
    public function __construct(
        public ?string $wasmBinary = null,   // path to .wasm file
        public ?string $wasmRunner = null,   // runner executable name/path
        /** The seconds a render may run before it stops with an EngineException: 5 when left out, no limit at 0. */
        public ?int    $timeout    = null,
        /**
         * The megabytes of memory a render may allocate, its data included, before it stops with an EngineException
         * that names the cap: 1024 when left out, from 1 to 4096.
         */
        public ?int    $maxMemoryMb = null,
    ) {
        if ($maxMemoryMb !== null && ($maxMemoryMb < 1 || $maxMemoryMb > self::MOST_MEMORY_MB)) {
            throw new \InvalidArgumentException(
                'maxMemoryMb must be a whole number of megabytes from 1 to ' . self::MOST_MEMORY_MB . ", not $maxMemoryMb",
            );
        }
    }
}
