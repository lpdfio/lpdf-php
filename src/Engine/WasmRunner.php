<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * Renders with the `wasmtime` program, which runs the engine's WebAssembly in a process of its own, for each render.
 * wasmtime compiles the engine to native code the first time, and keeps it in the SDK's folder for the renders after it
 * (`WasmCache`).
 */
final readonly class WasmRunner implements EngineRunner
{
    /** The seconds a render may run when `timeout` is left out. A timeout of 0 means no limit. */
    public const DEFAULT_TIMEOUT = 5;

    /** The megabytes a render may allocate when `maxMemoryMb` is left out. */
    public const DEFAULT_MAX_MEMORY_MB = 1024;

    /**
     * @param ?string $ffiRefused   Why the native library wasn't used, when `auto` chose this runner in its place: what
     *                              a render that can't start the program says too.
     * @param ?string $wasmCacheDir The folder where wasmtime keeps the compiled engine, `lpdf-wasmtime-<uid>` in the
     *                              temp directory when null (`WasmCache`).
     */
    public function __construct(
        private readonly string $wasmBinary,
        private readonly string $wasmRunner = 'wasmtime',
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
        private readonly int $maxMemoryMb = self::DEFAULT_MAX_MEMORY_MB,
        private readonly ?string $ffiRefused = null,
        private readonly ?string $wasmCacheDir = null,
    ) {}

    public function name(): string
    {
        return Runner::Wasm;
    }

    /**
     * @param  array<string, mixed> $payload  Already-built request array.
     * @return array<string, mixed>            Decoded response.
     * @throws EngineException On process or render error, when the engine crashes, when the process runs past the
     *                         timeout, when the program can't be started, or when it can't create its cache directory.
     */
    public function invoke(array $payload): array
    {
        $payload['max_memory_mb'] = $this->maxMemoryMb;

        // A host that lists proc_open in disable_functions has no such function.
        if (!\function_exists('proc_open')) {
            throw new EngineException($this->notStarted(
                'proc_open is disabled in php.ini',
                'remove proc_open from disable_functions in php.ini',
            ));
        }

        // Checked at each render, since a cleaner of the temp directory may remove the folder between two.
        $cache = WasmCache::open($this->wasmCacheDir);
        $command = [$this->wasmRunner, 'run'];
        if ($cache instanceof WasmCache) {
            array_push($command, '-C', "cache-config={$cache->configFile}");
        }
        $command[] = $this->wasmBinary;

        // Without the program, proc_open warns and returns false: the error below says what to do instead.
        error_clear_last();
        $proc = @proc_open(
            $command,
            [
                0 => ['pipe', 'r'],   // stdin
                1 => ['pipe', 'w'],   // stdout
                2 => ['pipe', 'w'],   // stderr
            ],
            $pipes,
        );

        if ($proc === false) {
            throw new EngineException($this->notStarted(error_get_last()['message'] ?? 'proc_open failed'));
        }

        fwrite($pipes[0], json_encode($payload, JSON_THROW_ON_ERROR));
        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $out     = '';
        $err     = '';
        // A timeout of 0 sets no deadline: the wait for output then has no limit either.
        $deadline = $this->timeout > 0 ? microtime(true) + $this->timeout : null;

        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $read    = array_filter([$pipes[1], $pipes[2]], fn($p) => !feof($p));
            $write   = null;
            $except  = null;
            $sec     = null;
            $usec    = null;
            if ($deadline !== null) {
                $remaining = $deadline - microtime(true);
                if ($remaining <= 0) {
                    proc_terminate($proc);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($proc);
                    throw EngineException::timedOut($this->timeout);
                }
                $sec  = (int) $remaining;
                $usec = (int)(($remaining - $sec) * 1_000_000);
            }
            if (stream_select($read, $write, $except, $sec, $usec) === false) {
                break;
            }
            foreach ($read as $r) {
                $chunk = fread($r, 8192);
                if ($chunk !== false) {
                    if ($r === $pipes[1]) { $out .= $chunk; }
                    else                  { $err .= $chunk; }
                }
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);

        // wasmtime reports a trap on stderr and exits with an error: a crash, by a bug, a stack that ran out, or memory
        // that did before the cap stopped the render. Each render runs a process of its own, so the next one starts on
        // a fresh engine.
        if ($exitCode !== 0 && str_contains($err, 'wasm trap')) {
            throw new EngineException(EngineException::CRASHED, 0, new \RuntimeException(trim($err)));
        }

        // Before PHP 8.3, proc_open starts the program in a child process, which exits with 127 when there is no such
        // program, where later versions return false above.
        if ($exitCode === 127 && $out === '') {
            $why = 'it exited with 127, as a missing program does' . (trim($err) === '' ? '' : ': ' . trim($err));
            throw new EngineException($this->notStarted($why));
        }

        // wasmtime stops before it runs the engine when it can't create its cache directory: in the SDK's folder, or in
        // its own default place, a home folder PHP's user may not write, where the SDK gave it none.
        if ($out === '' && str_contains($err, 'failed to create cache directory')) {
            throw new EngineException(self::noCacheDirectory($err, $cache));
        }

        if ($out === '') {
            throw new EngineException("WASI process produced no output. Stderr: $err");
        }

        $response = json_decode($out, true, 512, JSON_THROW_ON_ERROR);

        if (isset($response['error'])) {
            throw new EngineException("lpdf render error: {$response['error']}");
        }

        return $response;
    }

    /**
     * The error of a render that wasmtime didn't run because it can't create its cache directory, as its `$err` says,
     * with `$cache`'s reason where the SDK gave it no folder, and what would give it one: the POSIX extension where it
     * is missing, since the SDK then uses no folder at all, on Windows a profile PHP's user can write, or else another
     * `wasmCacheDir`.
     */
    private static function noCacheDirectory(string $err, WasmCache|string $cache): string
    {
        // wasmtime's error and its causes, each on lines of its own, on one line.
        $lines = array_filter(
            array_map('trim', explode("\n", $err)),
            static fn (string $line): bool => $line !== '' && $line !== 'Caused by:',
        );
        $detail = implode(': ', array_map(
            static fn (string $line): string => (string) preg_replace('/^(Error: |\d+: )/', '', $line),
            $lines,
        ));
        $message = "wasmtime could not create its cache directory, so it didn't render ($detail)";
        if (\is_string($cache)) {
            $message .= ". The SDK gave it no folder of its own, since $cache";
        }
        if ($cache === WasmCache::NO_POSIX) {
            return "$message. Load PHP's POSIX extension (ext-posix), so the SDK can give wasmtime a folder of its own.";
        }
        if ($cache === WasmCache::ON_WINDOWS) {
            return "$message. Let PHP's user write its profile, where wasmtime keeps its cache on Windows.";
        }
        return "$message. Set wasmCacheDir in EngineOptions to a folder PHP's user can make, or one it owns and can "
            . "write that group and others can't open.";
    }

    /** The error of a render whose program didn't start, which PHP explained with `$why`, and `$fix` would start. */
    private function notStarted(string $why, string $fix = 'install wasmtime where PHP finds it'): string
    {
        $message = "Lpdf could not start '{$this->wasmRunner}' to render ($why): $fix";
        if ($this->ffiRefused === null) {
            return "$message.";
        }
        // `auto` came here because the native library can't be used: either way would render.
        return "$message, or let PHP load the native library, which it didn't because {$this->ffiRefused}.";
    }
}
