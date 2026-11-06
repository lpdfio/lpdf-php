<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\Runner;
use Lpdf\L;
use PHPUnit\Framework\TestCase;

/**
 * Which runner renders: `auto` picks the native library where it loads and `wasmtime` otherwise, `ffi` says why the
 * native library can't load, and `PdfEngine::runner()` says which one renders.
 *
 * What `auto` picks depends on the machine, so CI says what it expects in each job (`RunnerSupport`): the native
 * library on Linux with glibc, and `wasmtime` without the FFI extension, with `ffi.enable` refused, under PHP-FPM, and on
 * Alpine.
 */
final class RunnerTest extends TestCase
{
    /** Prints the runner `auto` picks, then a newline, for `inPhpOfItsOwn`. */
    private const PICK = 'echo Lpdf\L::engine()->runner(), "\n";';

    /** Prints why `runner: 'ffi'` can't render, for `inPhpOfItsOwn`. */
    private const FFI_REFUSAL = 'try { Lpdf\L::engine(new Lpdf\Engine\EngineOptions(runner: "ffi"))->runner(); }'
        . ' catch (Lpdf\Engine\EngineException $e) { echo $e->getMessage(); }';

    private const MINIMAL =
        '<lpdf version="1"><document><section><layout><text>x</text></layout></section></document></lpdf>';

    public function testAutoPicksTheRunnerTheJobExpectsAndItRendersTheStoredHash(): void
    {
        $expected = RunnerSupport::expectedAuto() ?? $this->markTestSkipped('LPDF_TEST_AUTO says what auto picks in a CI job.');
        $this->needsFixtures();
        $engine = L::engine()->setLicenseKey('test-key');

        $this->assertSame($expected, $engine->runner());
        SnapshotHelper::compareOrUpdate('bench_xs', $engine->render(self::fixture('bench_xs')));
    }

    public function testRunnerFfiThrowsTheCheckThatFailedWhereTheLibraryCantLoad(): void
    {
        $expected = RunnerSupport::expectedFfiRefusal()
            ?? $this->markTestSkipped('LPDF_TEST_FFI_REFUSED says why the library can\'t load in a CI job.');
        $engine = L::engine(new EngineOptions(runner: Runner::Ffi));

        foreach ([static fn () => $engine->runner(), static fn () => $engine->render(self::MINIMAL)] as $call) {
            try {
                $call();
                $this->fail('runner: ffi rendered where the native library can\'t load');
            } catch (EngineException $e) {
                $this->assertStringStartsWith("The native library can't render here: ", $e->getMessage());
                $this->assertStringContainsString($expected, $e->getMessage());
            }
        }
    }

    public function testRunnerFfiRendersWithTheNativeLibrary(): void
    {
        RunnerSupport::need(Runner::Ffi);
        $engine = L::engine(new EngineOptions(runner: Runner::Ffi));

        $this->assertSame(Runner::Ffi, $engine->runner());
        $this->assertStringStartsWith('%PDF-', $engine->render(self::MINIMAL));
    }

    public function testRunnerWasmRendersWithWasmtime(): void
    {
        RunnerSupport::need(Runner::Wasm);
        $engine = L::engine(new EngineOptions(runner: Runner::Wasm));

        $this->assertSame(Runner::Wasm, $engine->runner());
        $this->assertStringStartsWith('%PDF-', $engine->render(self::MINIMAL));
    }

    public function testALibraryThatIsNotAtItsPathIsNamed(): void
    {
        RunnerSupport::need(Runner::Ffi);
        $engine = L::engine(new EngineOptions(runner: Runner::Ffi, libPath: '/nowhere/liblpdf.so'));

        $this->expectException(EngineException::class);
        $this->expectExceptionMessage("The native library can't render here: the native library is not at /nowhere/liblpdf.so.");

        $engine->render(self::MINIMAL);
    }

    public function testWhenNeitherRunnerCanRenderTheErrorNamesBothFixes(): void
    {
        $engine = L::engine(new EngineOptions(libPath: '/nowhere/liblpdf.so', wasmRunner: '/nowhere/wasmtime'));

        try {
            $engine->render(self::MINIMAL);
            $this->fail('a render with neither runner');
        } catch (EngineException $e) {
            $this->assertStringStartsWith("Lpdf could not start '/nowhere/wasmtime' to render (", $e->getMessage());
            $this->assertStringContainsString('install wasmtime where PHP finds it, or let PHP load the native library, which '
                . "it didn't because ", $e->getMessage());
        }
    }

    public function testWhereProcOpenIsDisabledTheErrorSaysSo(): void
    {
        $render = 'try { Lpdf\L::engine(new Lpdf\Engine\EngineOptions(libPath: "/nowhere/liblpdf.so"))'
            . '->render(' . var_export(self::MINIMAL, true) . '); }'
            . ' catch (Lpdf\Engine\EngineException $e) { echo $e->getMessage(); }';

        $output = self::inPhpOfItsOwn('-d disable_functions=proc_open', $render);

        $this->assertStringStartsWith("Lpdf could not start 'wasmtime' to render (proc_open is disabled in php.ini): remove "
            . "proc_open from disable_functions in php.ini, or let PHP load the native library, which it didn't because ",
            $output);
    }

    /**
     * Where open_basedir hides /proc and /lib, as on a shared host, `auto` picks as it does without it: musl is still
     * told apart from glibc. It runs with this PHP's `ffi.enable`.
     */
    public function testUnderOpenBasedirAutoPicksTheRunnerTheJobExpects(): void
    {
        $expected = RunnerSupport::expectedAuto() ?? $this->markTestSkipped('LPDF_TEST_AUTO says what auto picks in a CI job.');
        $ffi = \extension_loaded('ffi') ? ' -d ffi.enable=' . escapeshellarg((string) \ini_get('ffi.enable')) : '';

        $basedir = '-d open_basedir=' . escapeshellarg(\dirname(__DIR__));
        $output = self::inPhpOfItsOwn($basedir . $ffi, 'echo Lpdf\L::engine()->runner();');

        $this->assertSame($expected, $output);
    }

    /**
     * Where open_basedir hides /proc and `ffi.enable` refuses FFI too, `runner: 'ffi'` gives the reason the job
     * expects: musl on Alpine, not `ffi.enable`, whose fix would only lead to the musl error; `ffi.enable` on glibc.
     */
    public function testUnderOpenBasedirWithFfiRefusedRunnerFfiGivesTheReasonTheJobExpects(): void
    {
        $expected = RunnerSupport::expectedFfiRefusal()
            ?? $this->markTestSkipped('LPDF_TEST_FFI_REFUSED says why the library can\'t load in a CI job.');
        $basedir = '-d open_basedir=' . escapeshellarg(\dirname(__DIR__));

        $output = self::inPhpOfItsOwn("$basedir -d ffi.enable=false", self::FFI_REFUSAL);

        $this->assertStringContainsString($expected, $output);
    }

    /** Shared hosts list `php_uname` in `disable_functions`, where PHP 8 has no such function. */
    public function testWherePhpUnameIsDisabledAutoRendersWithWasmtimeAndFfiSaysWhy(): void
    {
        self::needsTheChecksOnLinux();

        $output = self::inPhpOfItsOwn('-d disable_functions=php_uname', self::PICK . self::FFI_REFUSAL);

        $this->assertSame(Runner::Wasm . "\nThe native library can't render here: php_uname is disabled in php.ini, "
            . 'and the SDK needs it to tell this CPU.', $output);
    }

    /** `glob` is the last sign of musl, where open_basedir hides /proc and FFI is refused. */
    public function testWhereGlobIsDisabledAutoRendersWithWasmtimeAndFfiNamesFfiEnable(): void
    {
        self::needsTheChecksOnLinux();
        $settings = '-d disable_functions=glob -d open_basedir=' . escapeshellarg(\dirname(__DIR__))
            . ' -d ffi.enable=false';

        $output = self::inPhpOfItsOwn($settings, self::PICK . self::FFI_REFUSAL);

        $this->assertStringStartsWith(Runner::Wasm . "\nThe native library can't render here: ", $output);
        $this->assertStringContainsString('restricted by "ffi.enable"', $output);
    }

    public function testAnUnknownRunnerIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("runner must be 'auto', 'ffi', 'wasm', not 'native'");

        new EngineOptions(runner: 'native');
    }

    /**
     * Under PHP-FPM, `ffi.enable` at its default allows FFI only in preloaded files, so `auto` renders with wasmtime.
     * The pool runs as `www-data`, as the official image's does, where wasmtime can't write its own cache in the
     * user's home, `/var/www`: it keeps the compiled engine in the SDK's folder in the temp directory instead, and
     * the second render compiles nothing.
     */
    public function testUnderPhpFpmWithFfiEnableAtItsDefaultAutoRendersWithWasmtime(): void
    {
        $fpm = getenv('LPDF_TEST_FPM') ?: $this->markTestSkipped('LPDF_TEST_FPM names php-fpm in the CI job that runs it.');
        $this->needsFixtures();
        $hash = trim((string) file_get_contents(SnapshotHelper::snapshots() . '/bench_xs.pdf.sha256'));

        [$first, $second] = self::requestsUnderFpm($fpm, __DIR__ . '/fpm/render.php', 2);

        $this->assertSame('www-data', $first['user']);
        $this->assertSame('preload', $first['ffi.enable']);
        $this->assertStringContainsString('restricted by "ffi.enable"', (string) $first['ffiRefused']);
        $this->assertStringEndsWith(': set ffi.enable=true in php.ini.', (string) $first['ffiRefused']);
        $this->assertSame(Runner::Wasm, $first['runner'] ?? null);
        $this->assertNull($first['error'] ?? null);
        $this->assertSame($hash, $first['hash']);
        $this->assertNotEmpty($first['compiled']);

        $this->assertNull($second['error'] ?? null);
        $this->assertSame($hash, $second['hash']);
        $this->assertSame($first['compiled'], $second['compiled']);
    }

    /**
     * @return list<array<string, mixed>> What `$script` answers to each of `$count` GET requests, one after another,
     *                                    under a pool of `$fpm` of its own.
     */
    private static function requestsUnderFpm(string $fpm, string $script, int $count): array
    {
        $dir = sys_get_temp_dir() . '/lpdf-fpm-' . getmypid();
        @mkdir($dir);
        $port = 9701;
        // The pool's defaults but PATH, which leads to wasmtime: the environment is cleared, HOME with it, so wasmtime's
        // own cache would be in www-data's home.
        $pool = ['[global]', "pid = $dir/fpm.pid", "error_log = $dir/fpm.log", 'daemonize = yes', '[lpdf]',
            "listen = 127.0.0.1:$port", 'pm = static', 'pm.max_children = 1', 'user = www-data', 'group = www-data',
            'env[PATH] = ' . getenv('PATH')];
        file_put_contents("$dir/fpm.conf", implode("\n", $pool) . "\n");
        exec(escapeshellarg($fpm) . ' -y ' . escapeshellarg("$dir/fpm.conf") . ' 2>&1', $output, $code);
        self::assertSame(0, $code, implode("\n", $output));
        $answers = [];
        try {
            for ($request = 0; $request < $count; $request++) {
                $response = '';
                for ($try = 0; $try < 50 && !str_contains($response, '{'); $try++) {
                    usleep(100_000);
                    $response = (string) shell_exec(sprintf(
                        'SCRIPT_FILENAME=%s REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:%d 2>&1',
                        escapeshellarg($script),
                        $port,
                    ));
                }
                $answer = json_decode(substr($response, (int) strpos($response, '{')), true);
                self::assertIsArray($answer, $response . "\n" . @file_get_contents("$dir/fpm.log"));
                $answers[] = $answer;
            }
        } finally {
            $pid = (int) @file_get_contents("$dir/fpm.pid");
            if ($pid > 0) {
                exec("kill $pid");
            }
        }
        return $answers;
    }

    /**
     * What `$code` prints in a PHP of its own, started with `$settings`, such as `-d disable_functions=…`: php.ini's
     * `disable_functions` and `open_basedir` are set only when PHP starts.
     */
    private static function inPhpOfItsOwn(string $settings, string $code): string
    {
        $code = 'require ' . var_export(\dirname(__DIR__) . '/vendor/autoload.php', true) . "; $code";
        exec(escapeshellarg(\PHP_BINARY) . " $settings -r " . escapeshellarg($code) . ' 2>&1', $output);
        return implode("\n", $output);
    }

    /** The checks before the library loads reach `php_uname` and `glob` only with the FFI extension, on Linux. */
    private static function needsTheChecksOnLinux(): void
    {
        if (!\extension_loaded('ffi') || \PHP_OS_FAMILY !== 'Linux') {
            self::markTestSkipped('Without FFI, or off Linux, the checks stop before php_uname and glob.');
        }
    }

    private function needsFixtures(): void
    {
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
    }

    private static function fixture(string $name): string
    {
        return (string) file_get_contents(SnapshotHelper::fixtures() . "/$name.xml");
    }
}
