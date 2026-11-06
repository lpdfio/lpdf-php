<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * Renders with the engine's native library, loaded through PHP's FFI and called in PHP's own process: no program to
 * install, and no process for each render.
 *
 * The library is in the package for Linux with glibc, on x86_64 and aarch64 (`resources/lib`), and is loaded once in
 * each process. The engine stops a render that runs past its timeout itself, since there is no process to end, and
 * one that goes past its memory cap. Unlike `wasmtime`, the library runs outside WebAssembly's sandbox, with the rights
 * of the PHP process.
 */
final class FfiRunner implements EngineRunner
{
    /** The library's two functions; a PHP string is passed as `const char *`. */
    private const CDEF = <<<'C'
        uint8_t *lpdf_invoke(const char *payload, size_t len, size_t *out_len);
        void lpdf_free(uint8_t *ptr, size_t len);
        C;

    /** The CPUs the library is built for, by what `php_uname('m')` says on Linux. */
    private const CPUS = ['x86_64' => 'x86_64', 'aarch64' => 'aarch64', 'arm64' => 'aarch64'];

    /** @var array<string, \FFI|string> The library loaded from each path in this process, or why it can't be. */
    private static array $libraries = [];

    private function __construct(
        private readonly \FFI $library,
        private readonly int $timeout,
        private readonly int $maxMemoryMb,
    ) {}

    /**
     * The runner for the library at `$libPath`, or the one in the package for this CPU, loaded if it isn't yet.
     *
     * @param int $timeout     The seconds a render may run, 0 for no limit.
     * @param int $maxMemoryMb The megabytes a render may allocate.
     * @return self|string The runner, or why the library can't be used here: the check that failed.
     */
    public static function open(?string $libPath, int $timeout, int $maxMemoryMb): self|string
    {
        $library = self::load($libPath);
        return $library instanceof \FFI ? new self($library, $timeout, $maxMemoryMb) : $library;
    }

    public function name(): string
    {
        return Runner::Ffi;
    }

    /**
     * @param  array<string, mixed> $payload  Already-built request array.
     * @return array<string, mixed>            Decoded response.
     * @throws EngineException On a render error, when the render runs past the timeout or the memory cap, or when the
     *                         engine panics.
     */
    public function invoke(array $payload): array
    {
        $payload['timeout'] = $this->timeout;
        $payload['max_memory_mb'] = $this->maxMemoryMb;
        $request = json_encode($payload, JSON_THROW_ON_ERROR);

        $length = $this->library->new('size_t');
        $answer = $this->library->lpdf_invoke($request, \strlen($request), \FFI::addr($length));
        try {
            $out = \FFI::string($answer, $length->cdata);
        } finally {
            $this->library->lpdf_free($answer, $length->cdata);
        }

        $response = json_decode($out, true, 512, JSON_THROW_ON_ERROR);
        if (($response['timed_out'] ?? false) === true) {
            throw EngineException::timedOut($this->timeout);
        }
        // A panic in the engine, which it caught: a bug, reported as the crash of wasmtime's engine is.
        if (($response['crashed'] ?? false) === true) {
            throw new EngineException(EngineException::CRASHED, 0, new \RuntimeException((string) $response['error']));
        }
        if (isset($response['error'])) {
            throw new EngineException("lpdf render error: {$response['error']}");
        }
        return $response;
    }

    /** The library in the package for this CPU, or null on a CPU it isn't built for. */
    public static function packagedLibrary(): ?string
    {
        $cpu = self::CPUS[php_uname('m')] ?? null;
        return $cpu === null ? null : \dirname(__DIR__, 2) . "/resources/lib/liblpdf-linux-$cpu.so";
    }

    /** The library at `$libPath`, or the packaged one, loaded once in this process; or why it can't be used here. */
    private static function load(?string $libPath): \FFI|string
    {
        $key = $libPath ?? '';
        return self::$libraries[$key] ??= self::unavailable($libPath) ?? self::cdef($libPath ?? (string) self::packagedLibrary());
    }

    /**
     * Why the library can't be used here, before it is loaded, or null when nothing stands in the way. The extension
     * and the CPU are checked first, since what PHP says when it loads a library without them misleads.
     */
    private static function unavailable(?string $libPath): ?string
    {
        if (!\extension_loaded('ffi')) {
            return "PHP's FFI extension is not loaded";
        }
        if (\PHP_OS_FAMILY !== 'Linux') {
            return 'the native library is for Linux, and PHP runs on ' . \PHP_OS_FAMILY;
        }
        $cpu = php_uname('m');
        if (!isset(self::CPUS[$cpu])) {
            return "the native library is for x86_64 and aarch64, and this CPU is $cpu";
        }
        if (self::onMusl()) {
            return "the native library is for glibc, and this system's C library is musl, as on Alpine: musl isn't "
                . "supported, so render with the wasm runner";
        }
        $path = $libPath ?? (string) self::packagedLibrary();
        if (!is_file($path)) {
            return "the native library is not at $path";
        }
        return null;
    }

    /**
     * Whether PHP runs on musl, Alpine's C library, which isn't supported: the library is built for glibc. The loader
     * mapped into PHP's own process says, since musl's loader in /lib doesn't: Debian's musl package puts it there
     * beside glibc's. Where open_basedir hides /proc, and /lib with it, the C library itself says, as open_basedir
     * doesn't govern FFI: only glibc has `gnu_get_libc_version`. Where `ffi.enable` refuses FFI, loading the library
     * says so next.
     */
    private static function onMusl(): bool
    {
        $maps = @file_get_contents('/proc/self/maps');
        if ($maps !== false) {
            return str_contains($maps, '/ld-musl-');
        }
        try {
            \FFI::cdef('const char *gnu_get_libc_version(void);');
            return false;
        } catch (\FFI\Exception $e) {
            return str_contains($e->getMessage(), 'gnu_get_libc_version');
        }
    }

    /** The library at `$path`, loaded, or what loading it threw. */
    private static function cdef(string $path): \FFI|string
    {
        try {
            return \FFI::cdef(self::CDEF, $path);
        } catch (\FFI\Exception $e) {
            // The default `ffi.enable=preload` allows FFI on the command line only.
            $fix = str_contains($e->getMessage(), 'ffi.enable') ? ': set ffi.enable=true in php.ini' : '';
            return "PHP could not load the native library at $path: {$e->getMessage()}$fix";
        }
    }
}
