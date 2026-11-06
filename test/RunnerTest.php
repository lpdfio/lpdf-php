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

    /** `disable_functions` can be set only when PHP starts, so the render runs in a PHP of its own. */
    public function testWhereProcOpenIsDisabledTheErrorSaysSo(): void
    {
        $render = 'require ' . var_export(\dirname(__DIR__) . '/vendor/autoload.php', true) . ';'
            . ' try { Lpdf\L::engine(new Lpdf\Engine\EngineOptions(libPath: "/nowhere/liblpdf.so"))->render('
            . var_export(self::MINIMAL, true) . '); } catch (Lpdf\Engine\EngineException $e) { echo $e->getMessage(); }';
        exec(escapeshellarg(\PHP_BINARY) . ' -d disable_functions=proc_open -r ' . escapeshellarg($render) . ' 2>&1', $output);

        $this->assertStringStartsWith("Lpdf could not start 'wasmtime' to render (proc_open is disabled in php.ini): remove "
            . "proc_open from disable_functions in php.ini, or let PHP load the native library, which it didn't because ",
            implode("\n", $output));
    }

    /**
     * Where open_basedir hides /proc and /lib, as on a shared host, `auto` picks as it does without it: musl is still
     * told apart from glibc. open_basedir can be set only when PHP starts, so the pick runs in a PHP of its own, with
     * this one's `ffi.enable`.
     */
    public function testUnderOpenBasedirAutoPicksTheRunnerTheJobExpects(): void
    {
        $expected = RunnerSupport::expectedAuto() ?? $this->markTestSkipped('LPDF_TEST_AUTO says what auto picks in a CI job.');
        $app = \dirname(__DIR__);
        $ffi = \extension_loaded('ffi') ? ' -d ffi.enable=' . escapeshellarg((string) \ini_get('ffi.enable')) : '';
        $pick = 'require ' . var_export("$app/vendor/autoload.php", true) . '; echo Lpdf\L::engine()->runner();';
        exec(escapeshellarg(\PHP_BINARY) . ' -d open_basedir=' . escapeshellarg($app) . $ffi . ' -r '
            . escapeshellarg($pick) . ' 2>&1', $output);

        $this->assertSame($expected, implode("\n", $output));
    }

    /**
     * On Alpine where open_basedir hides /proc and `ffi.enable` refuses FFI too, `runner: 'ffi'` names musl, not
     * `ffi.enable`, whose fix would only lead to the musl error.
     */
    public function testUnderOpenBasedirWithFfiRefusedRunnerFfiNamesMusl(): void
    {
        $expected = RunnerSupport::expectedFfiRefusal();
        if ($expected === null || !str_contains($expected, 'musl')) {
            $this->markTestSkipped('The Alpine job runs it: LPDF_TEST_FFI_REFUSED names musl there.');
        }
        $app = \dirname(__DIR__);
        $refusal = 'require ' . var_export("$app/vendor/autoload.php", true) . ';'
            . ' try { Lpdf\L::engine(new Lpdf\Engine\EngineOptions(runner: "ffi"))->runner(); }'
            . ' catch (Lpdf\Engine\EngineException $e) { echo $e->getMessage(); }';
        exec(escapeshellarg(\PHP_BINARY) . ' -d open_basedir=' . escapeshellarg($app) . ' -d ffi.enable=false -r '
            . escapeshellarg($refusal) . ' 2>&1', $output);

        $this->assertStringContainsString($expected, implode("\n", $output));
    }

    public function testAnUnknownRunnerIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("runner must be 'auto', 'ffi', 'wasm', not 'native'");

        new EngineOptions(runner: 'native');
    }

    /**
     * Under PHP-FPM, `ffi.enable` at its default allows FFI only in preloaded files, so `auto` renders with wasmtime.
     * The pool runs as this user: as `www-data`, wasmtime can't create its cache in the official image, which is
     * wasmtime's own problem with that image.
     */
    public function testUnderPhpFpmWithFfiEnableAtItsDefaultAutoRendersWithWasmtime(): void
    {
        $fpm = getenv('LPDF_TEST_FPM') ?: $this->markTestSkipped('LPDF_TEST_FPM names php-fpm in the CI job that runs it.');
        $this->needsFixtures();

        $answer = self::requestUnderFpm($fpm, __DIR__ . '/fpm/render.php');

        $this->assertSame('preload', $answer['ffi.enable']);
        $this->assertStringContainsString('restricted by "ffi.enable"', (string) $answer['ffiRefused']);
        $this->assertStringEndsWith(': set ffi.enable=true in php.ini.', (string) $answer['ffiRefused']);
        $this->assertSame(Runner::Wasm, $answer['runner'] ?? $answer['error'] ?? null);
        $this->assertSame(trim((string) file_get_contents(SnapshotHelper::snapshots() . '/bench_xs.pdf.sha256')), $answer['hash']);
    }

    /** @return array<string, mixed> What `$script` answers to one GET request under a pool of `$fpm` of its own. */
    private static function requestUnderFpm(string $fpm, string $script): array
    {
        $dir = sys_get_temp_dir() . '/lpdf-fpm-' . getmypid();
        @mkdir($dir);
        $port = 9701;
        // Kept: PATH and HOME, for wasmtime and its cache.
        $pool = ['[global]', "pid = $dir/fpm.pid", "error_log = $dir/fpm.log", 'daemonize = yes', '[lpdf]',
            "listen = 127.0.0.1:$port", 'pm = static', 'pm.max_children = 1', 'clear_env = no'];
        if (trim((string) shell_exec('id -u')) === '0') {
            $pool[] = 'user = root';
        }
        file_put_contents("$dir/fpm.conf", implode("\n", $pool) . "\n");
        exec(escapeshellarg($fpm) . ' -R -y ' . escapeshellarg("$dir/fpm.conf") . ' 2>&1', $output, $code);
        self::assertSame(0, $code, implode("\n", $output));
        try {
            $response = '';
            for ($try = 0; $try < 50 && !str_contains($response, '{'); $try++) {
                usleep(100_000);
                $response = (string) shell_exec(sprintf(
                    'SCRIPT_FILENAME=%s REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:%d 2>&1',
                    escapeshellarg($script),
                    $port,
                ));
            }
        } finally {
            $pid = (int) @file_get_contents("$dir/fpm.pid");
            if ($pid > 0) {
                exec("kill $pid");
            }
        }
        $body = substr($response, (int) strpos($response, '{'));
        $answer = json_decode($body, true);
        self::assertIsArray($answer, $response . "\n" . @file_get_contents("$dir/fpm.log"));
        return $answer;
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
