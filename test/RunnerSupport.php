<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\FfiRunner;
use Lpdf\Engine\Runner;
use PHPUnit\Framework\Assert;

/**
 * Which runners can render where the tests run, and what CI says they must.
 *
 * A test through a runner that can't render here is skipped, so the tests run on a machine without the FFI extension
 * or without wasmtime. CI says what each of its jobs expects in environment variables, and a test fails when the
 * machine doesn't match:
 *
 * - `LPDF_TEST_RUNNERS`: the runners that must render here, `ffi`, `wasm` or `ffi,wasm`.
 * - `LPDF_TEST_AUTO`: the runner `auto` must pick here, `ffi` or `wasm`.
 * - `LPDF_TEST_FFI_REFUSED`: text that the error of `runner: 'ffi'` must hold here, for a job where it can't load.
 */
final class RunnerSupport
{
    /** Skip the test when `$runner` can't render here, or fail it when `LPDF_TEST_RUNNERS` says it must. */
    public static function need(string $runner): void
    {
        $why = self::unavailable($runner);
        if ($why === null) {
            return;
        }
        if (\in_array($runner, self::required(), true)) {
            Assert::fail("LPDF_TEST_RUNNERS says the $runner runner renders here, and it can't: $why");
        }
        Assert::markTestSkipped("the $runner runner can't render here: $why");
    }

    /** Why `$runner` can't render here, or null when it can. */
    public static function unavailable(string $runner): ?string
    {
        if ($runner === Runner::Ffi) {
            $ffi = FfiRunner::open(null, 5, 1024);
            return $ffi instanceof FfiRunner ? null : $ffi;
        }
        return trim((string) shell_exec('command -v wasmtime')) === '' ? 'wasmtime is not on PATH' : null;
    }

    /** What `LPDF_TEST_AUTO` says `auto` picks here, or null when the job doesn't say. */
    public static function expectedAuto(): ?string
    {
        return self::env('LPDF_TEST_AUTO');
    }

    /** What `LPDF_TEST_FFI_REFUSED` says the error of `runner: 'ffi'` holds here, or null when the job doesn't say. */
    public static function expectedFfiRefusal(): ?string
    {
        return self::env('LPDF_TEST_FFI_REFUSED');
    }

    /** @return array<string, array{string}> Each runner, by name, for a data provider. */
    public static function runners(): array
    {
        return [Runner::Ffi => [Runner::Ffi], Runner::Wasm => [Runner::Wasm]];
    }

    /** @return list<string> */
    private static function required(): array
    {
        $runners = self::env('LPDF_TEST_RUNNERS');
        return $runners === null ? [] : array_map('trim', explode(',', $runners));
    }

    private static function env(string $name): ?string
    {
        $value = getenv($name);
        return $value === false || $value === '' ? null : $value;
    }
}
