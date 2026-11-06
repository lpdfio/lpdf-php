<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * What sends the engine a request and returns its answer: the native library through FFI (`FfiRunner`), or the
 * `wasmtime` program (`WasmRunner`).
 */
interface EngineRunner
{
    /**
     * @param  array<string, mixed> $payload  The request, as the engine reads it.
     * @return array<string, mixed>           The engine's answer.
     * @throws EngineException On a render error, past the timeout or the memory cap, when the engine crashes, or when
     *                         the runner can't start.
     */
    public function invoke(array $payload): array;

    /** Which runner this is: `Runner::Ffi` or `Runner::Wasm`. */
    public function name(): string;
}
