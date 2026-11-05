<?php

declare(strict_types=1);

namespace Lpdf\Engine;

final readonly class EngineOptions
{
    /** The most `maxMemoryMb` may be: 32-bit WebAssembly addresses 4 GB at most. */
    private const MOST_MEMORY_MB = 4096;

    /**
     * @throws \InvalidArgumentException When `maxMemoryMb` is below 1 or above 4096, or `runner` is not `auto`, `ffi`
     *                                   or `wasm`.
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
        /**
         * Which runner renders (`Runner`): `auto` when left out, the native library through PHP's FFI where it loads
         * and the `wasmtime` program otherwise; `ffi`, the native library or an EngineException that says why it
         * can't load; or `wasm`, the `wasmtime` program. `PdfEngine::runner()` says which one an engine renders with.
         */
        public ?string $runner = null,
        /** The native library to load through FFI, in place of the one in the package for this CPU. */
        public ?string $libPath = null,
    ) {
        if ($maxMemoryMb !== null && ($maxMemoryMb < 1 || $maxMemoryMb > self::MOST_MEMORY_MB)) {
            throw new \InvalidArgumentException(
                'maxMemoryMb must be a whole number of megabytes from 1 to ' . self::MOST_MEMORY_MB . ", not $maxMemoryMb",
            );
        }
        if ($runner !== null && !\in_array($runner, Runner::ALL, true)) {
            throw new \InvalidArgumentException(
                "runner must be '" . implode("', '", Runner::ALL) . "', not '$runner'",
            );
        }
    }

    /**
     * The runner these options render with: the native library where `runner` allows it and it loads, else
     * `wasmtime`.
     *
     * @throws EngineException With `runner: 'ffi'`, when the native library can't be used here: the check that failed.
     */
    public function engineRunner(): EngineRunner
    {
        $timeout = $this->timeout ?? WasmRunner::DEFAULT_TIMEOUT;
        $maxMemoryMb = $this->maxMemoryMb ?? WasmRunner::DEFAULT_MAX_MEMORY_MB;
        $runner = $this->runner ?? Runner::Auto;

        $ffiRefused = null;
        if ($runner !== Runner::Wasm) {
            $ffi = FfiRunner::open($this->libPath, $timeout, $maxMemoryMb);
            if ($ffi instanceof FfiRunner) {
                return $ffi;
            }
            if ($runner === Runner::Ffi) {
                throw new EngineException("The native library can't render here: $ffi.");
            }
            $ffiRefused = $ffi;
        }
        return new WasmRunner(
            wasmBinary: $this->wasmBinary ?? \dirname(__DIR__, 2) . '/resources/lpdf-wasi.wasm',
            wasmRunner: $this->wasmRunner ?? 'wasmtime',
            timeout: $timeout,
            maxMemoryMb: $maxMemoryMb,
            ffiRefused: $ffiRefused,
        );
    }
}
