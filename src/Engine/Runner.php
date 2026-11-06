<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * The runners that render: the values of `EngineOptions::$runner`, and what `PdfEngine::runner()` answers.
 */
final class Runner
{
    /**
     * The native library through PHP's FFI where it loads, else `wasmtime`: the default. The native library loads on
     * Linux with glibc 2.17 or newer, on x86_64 and arm64, with the FFI extension, which a web server allows with
     * `ffi.enable=true`.
     */
    public const Auto = 'auto';

    /**
     * The native library through PHP's FFI, in PHP's own process. Where it can't load, a render throws an
     * `EngineException` that names the check that failed.
     */
    public const Ffi = 'ffi';

    /**
     * The `wasmtime` program on `PATH`, which runs the engine's WebAssembly in a process of its own, for each render: the
     * runner for Alpine, macOS and Windows, and the one that keeps the engine in WebAssembly's sandbox.
     */
    public const Wasm = 'wasm';

    /** The values `EngineOptions::$runner` takes. */
    public const ALL = [self::Auto, self::Ffi, self::Wasm];
}
