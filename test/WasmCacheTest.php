<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\Runner;
use Lpdf\L;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The folder where `wasmtime` keeps the engine compiled to native code: made with mode 0700, and used only when PHP's
 * user owns it and no one else can open it. Most tests render with a stand-in `wasmtime`, a shell script that records
 * its arguments and answers a PDF, so they see whether the SDK passed `-C cache-config`.
 */
final class WasmCacheTest extends TestCase
{
    private const MINIMAL =
        '<lpdf version="1"><document><section><layout><text>x</text></layout></section></document></lpdf>';

    /** A folder of the test's own in the temp directory, removed after it. */
    private string $scratch;

    protected function setUp(): void
    {
        if (\PHP_OS_FAMILY === 'Windows' || !\function_exists('posix_geteuid')) {
            $this->markTestSkipped('The cache folder is checked through the POSIX extension, which Windows lacks.');
        }
        $this->scratch = sys_get_temp_dir() . '/lpdf-wasm-cache-test-' . bin2hex(random_bytes(6));
        mkdir($this->scratch, 0700);
    }

    protected function tearDown(): void
    {
        if (isset($this->scratch)) {
            exec('rm -rf ' . escapeshellarg($this->scratch));
        }
    }

    public function testAMissingWasmCacheDirIsMadeWithMode0700AndWasmtimeIsGivenItsConfig(): void
    {
        $folder = "$this->scratch/a/cache";

        $this->assertSame(["cache-config=$folder/config.toml"], $this->cacheOptions($folder));

        $this->assertSame(0700, fileperms($folder) & 0777);
        $this->assertSame(0700, fileperms(\dirname($folder)) & 0777);
        $this->assertSame("[cache]\ndirectory = \"$folder/cache\"\n", file_get_contents("$folder/config.toml"));
    }

    #[DataProvider('endings')]
    public function testAWasmCacheDirOfPhpsUserClosedToOthersIsUsedAsGiven(string $ending): void
    {
        $folder = "$this->scratch/cache";
        mkdir($folder, 0700);

        $this->assertSame(["cache-config=$folder/config.toml"], $this->cacheOptions($folder . $ending));
    }

    /** @return array<string, array{string}> How a folder's path may end. */
    public static function endings(): array
    {
        return ['as given' => [''], 'with a slash' => ['/'], 'with two slashes' => ['//']];
    }

    /** The temp directory is `sys_temp_dir`'s, set in a PHP of its own. */
    public function testWithoutWasmCacheDirTheFolderIsLpdfWasmtimeAndTheUserIdInTheTempDirectory(): void
    {
        $folder = "$this->scratch/lpdf-wasmtime-" . posix_geteuid();

        $output = $this->renderInPhpOfItsOwn('-d sys_temp_dir=' . escapeshellarg($this->scratch));

        $this->assertSame('', $output);
        $this->assertSame(["cache-config=$folder/config.toml"], $this->recordedCacheOptions());
        $this->assertSame(0700, fileperms($folder) & 0777);
    }

    /** With a slash at its end, the path names what the link leads to: a folder of PHP's user, closed to others. */
    #[DataProvider('endings')]
    public function testAWasmCacheDirThatIsALinkIsNotUsed(string $ending): void
    {
        mkdir("$this->scratch/real", 0700);
        symlink("$this->scratch/real", "$this->scratch/link");

        $this->assertSame([], $this->cacheOptions("$this->scratch/link" . $ending));
        $this->assertFileDoesNotExist("$this->scratch/real/config.toml");
    }

    public function testAWasmCacheDirOfAnotherUserIsNotUsed(): void
    {
        if (posix_geteuid() !== 0) {
            $this->markTestSkipped('Only root can give a folder to another user.');
        }
        $folder = "$this->scratch/cache";
        mkdir($folder, 0700);
        chown($folder, 65534);

        $this->assertSame([], $this->cacheOptions($folder));
        $this->assertFileDoesNotExist("$folder/config.toml");
    }

    #[DataProvider('openModes')]
    public function testAWasmCacheDirThatLetsGroupOrOthersInIsNotUsed(int $mode): void
    {
        $folder = "$this->scratch/cache";
        mkdir($folder);
        chmod($folder, $mode);

        $this->assertSame([], $this->cacheOptions($folder));
        $this->assertSame($mode, fileperms($folder) & 0777);
        $this->assertFileDoesNotExist("$folder/config.toml");
    }

    /** @return array<string, array{int}> */
    public static function openModes(): array
    {
        return ['group' => [0750], 'others' => [0701], 'both' => [0755]];
    }

    /** `config.toml` is TOML, which is UTF-8, so a path that isn't can't be written in it. */
    public function testAWasmCacheDirWhosePathIsNotUtf8IsNotUsed(): void
    {
        $this->assertSame([], $this->cacheOptions("$this->scratch/caf\xe9"));
        $this->assertSame(['arguments', 'wasmtime'], array_values(array_diff(scandir($this->scratch), ['.', '..'])));
    }

    /** `config.toml` is TOML, which takes control characters only escaped, and `json_encode` leaves DEL as it is. */
    #[DataProvider('controlCharacters')]
    public function testAWasmCacheDirWhosePathHoldsAControlCharacterIsNotUsed(string $character): void
    {
        $this->assertSame([], $this->cacheOptions("$this->scratch/a{$character}b"));
        $this->assertSame(['arguments', 'wasmtime'], array_values(array_diff(scandir($this->scratch), ['.', '..'])));
    }

    /** @return array<string, array{string}> */
    public static function controlCharacters(): array
    {
        return ['DEL' => ["\x7f"], 'a newline' => ["\n"]];
    }

    /**
     * The real wasmtime, which takes an absolute cache directory only: a relative `wasmCacheDir` is taken from PHP's
     * working directory, which is also wasmtime's.
     */
    public function testARelativeWasmCacheDirIsTakenFromTheWorkingDirectory(): void
    {
        RunnerSupport::need(Runner::Wasm);
        $workingDirectory = (string) getcwd();
        chdir($this->scratch);
        try {
            $engine = L::engine(new EngineOptions(runner: Runner::Wasm, wasmCacheDir: 'relative/cache'));

            $this->assertStringStartsWith('%PDF-', $engine->render(self::MINIMAL));

            $folder = getcwd() . '/relative/cache';
            $this->assertSame("[cache]\ndirectory = \"$folder/cache\"\n", file_get_contents("$folder/config.toml"));
            $this->assertNotEmpty(self::compiled($folder));
        } finally {
            chdir($workingDirectory);
        }
    }

    /** wasmtime's `-C` splits its options on commas, so the path of the config can't hold one. */
    public function testAWasmCacheDirWithACommaInItsPathIsNotUsed(): void
    {
        $this->assertSame([], $this->cacheOptions("$this->scratch/a,b"));
        $this->assertDirectoryDoesNotExist("$this->scratch/a,b");
    }

    /** Without the POSIX extension the SDK can't tell who owns a folder: `posix_geteuid` is disabled. */
    public function testWithoutThePosixExtensionNoFolderIsUsed(): void
    {
        $output = $this->renderInPhpOfItsOwn('-d disable_functions=posix_geteuid');

        $this->assertSame('', $output);
        $this->assertSame([], $this->recordedCacheOptions());
    }

    /**
     * Without the POSIX extension no `wasmCacheDir` would help, so where wasmtime can't create its own cache either,
     * the error says to load the extension. The real wasmtime, with HOME a file.
     */
    public function testWithoutThePosixExtensionTheErrorSaysToLoadIt(): void
    {
        RunnerSupport::need(Runner::Wasm);
        $render = 'try { Lpdf\L::engine(new Lpdf\Engine\EngineOptions(runner: "wasm"))->render('
            . var_export(self::MINIMAL, true) . '); echo "rendered"; }'
            . ' catch (Lpdf\Engine\EngineException $e) { echo $e->getMessage(); }';

        $noHome = 'env -u XDG_CACHE_HOME HOME=/dev/null';

        $output = self::inPhpOfItsOwn('-d disable_functions=posix_geteuid', $render, $noHome);

        $this->assertStringStartsWith('wasmtime could not create its cache directory, so it didn\'t render (', $output);
        $this->assertStringEndsWith(". The SDK gave it no folder of its own, since PHP's POSIX extension is not loaded, "
            . 'and the SDK needs it to tell who owns the folder. Load PHP\'s POSIX extension (ext-posix), so the SDK can '
            . 'give wasmtime a folder of its own.', $output);
    }

    /**
     * The real wasmtime: the first render compiles the engine into the folder's `cache`, and the second compiles
     * nothing. `config.toml` stays: wasmtime's cleanup deletes what it doesn't know in `cache`, so it is beside it.
     */
    public function testWasmtimeKeepsTheCompiledEngineInTheFolderAndTheSecondRenderAddsNone(): void
    {
        RunnerSupport::need(Runner::Wasm);
        $folder = "$this->scratch/cache";
        $engine = L::engine(new EngineOptions(runner: Runner::Wasm, wasmCacheDir: $folder));

        $this->assertStringStartsWith('%PDF-', $engine->render(self::MINIMAL));
        $compiled = self::compiled($folder);
        $this->assertNotEmpty($compiled);

        $this->assertStringStartsWith('%PDF-', $engine->render(self::MINIMAL));
        $this->assertSame($compiled, self::compiled($folder));
        $this->assertFileExists("$folder/config.toml");
    }

    /**
     * Where the SDK can't use the folder, wasmtime keeps its cache in its own default place, which here it can't
     * create either: HOME is a file, as a home PHP's user can't write would be.
     */
    #[DataProvider('foldersPhpCantWrite')]
    public function testAWasmCacheDirPhpCantWriteGivesTheErrorThatNamesIt(string $case): void
    {
        RunnerSupport::need(Runner::Wasm);
        $folder = "$this->scratch/cache";
        if ($case === 'under a file') {
            touch("$this->scratch/file");
            $folder = "$this->scratch/file/cache";
            $why = "$folder can't be made (Not a directory)";
        } else {
            if (posix_geteuid() === 0) {
                $this->markTestSkipped('Root writes in a folder whatever its mode.');
            }
            mkdir($folder, 0500);
            $why = "$folder/config.toml can't be written (Failed to open stream: Permission denied)";
        }
        $home = getenv('HOME');
        $xdg = getenv('XDG_CACHE_HOME');
        putenv('HOME=/dev/null');
        putenv('XDG_CACHE_HOME');
        try {
            L::engine(new EngineOptions(runner: Runner::Wasm, wasmCacheDir: $folder))->render(self::MINIMAL);
            $this->fail('wasmtime rendered with no cache directory it could create');
        } catch (EngineException $e) {
            $this->assertStringStartsWith('wasmtime could not create its cache directory, so it didn\'t render (failed to '
                . 'create cache directory: /dev/null/', $e->getMessage());
            $this->assertStringContainsString("). The SDK gave it no folder of its own, since $why. ", $e->getMessage());
            $this->assertStringEndsWith('. Set wasmCacheDir in EngineOptions to a folder PHP\'s user can make, or one it '
                . 'owns and can write that group and others can\'t open.', $e->getMessage());
        } finally {
            putenv($home === false ? 'HOME' : "HOME=$home");
            putenv($xdg === false ? 'XDG_CACHE_HOME' : "XDG_CACHE_HOME=$xdg");
        }
    }

    /** @return array<string, array{string}> */
    public static function foldersPhpCantWrite(): array
    {
        return ['under a file' => ['under a file'], 'closed to its owner' => ['closed to its owner']];
    }

    /** @return list<string> The `-C` options the stand-in got for a render with `$folder` as `wasmCacheDir`. */
    private function cacheOptions(string $folder): array
    {
        $options = new EngineOptions(wasmRunner: $this->standIn(), runner: Runner::Wasm, wasmCacheDir: $folder);

        $this->assertSame('%PDF-', L::engine($options)->render(self::MINIMAL));

        return $this->recordedCacheOptions();
    }

    /** What a render through the stand-in, with the folder left out, prints in a PHP of its own (`inPhpOfItsOwn`). */
    private function renderInPhpOfItsOwn(string $settings): string
    {
        $options = 'new Lpdf\Engine\EngineOptions(wasmRunner: ' . var_export($this->standIn(), true) . ', runner: "wasm")';
        return self::inPhpOfItsOwn(
            $settings,
            "if (Lpdf\\L::engine($options)->render(" . var_export(self::MINIMAL, true) . ') !== "%PDF-") { echo "no PDF"; }',
        );
    }

    /**
     * What `$code` prints in a PHP of its own, started with `$settings` and after `$env`, such as `env HOME=…`:
     * php.ini's `sys_temp_dir` and `disable_functions` are set only when PHP starts.
     */
    private static function inPhpOfItsOwn(string $settings, string $code, string $env = ''): string
    {
        $code = 'require ' . var_export(\dirname(__DIR__) . '/vendor/autoload.php', true) . "; $code";
        exec("$env " . escapeshellarg(\PHP_BINARY) . " $settings -r " . escapeshellarg($code) . ' 2>&1', $output);
        return implode("\n", $output);
    }

    /** A stand-in for `wasmtime` that records its arguments, one to a line, and answers the bytes `%PDF-`. */
    private function standIn(): string
    {
        $path = "$this->scratch/wasmtime";
        $script = "#!/bin/sh\nprintf '%s\\n' \"\$@\" > " . escapeshellarg("$this->scratch/arguments")
            . "\ncat > /dev/null\nprintf '{\"pdf\": \"JVBERi0=\"}'\n";
        file_put_contents($path, $script);
        chmod($path, 0755);
        return $path;
    }

    /** @return list<string> The values of the `-C` options of the stand-in's last run. */
    private function recordedCacheOptions(): array
    {
        $arguments = explode("\n", trim((string) file_get_contents("$this->scratch/arguments")));
        $this->assertSame('run', $arguments[0]);
        $options = [];
        foreach ($arguments as $i => $argument) {
            if ($argument === '-C') {
                $options[] = $arguments[$i + 1];
            }
        }
        return $options;
    }

    /**
     * @return list<string> The engines compiled in `$folder`'s cache: its files with no dot in their name, since the
     *                      `.stats` and `.wip-` files beside them are wasmtime's bookkeeping.
     */
    private static function compiled(string $folder): array
    {
        $files = array_map('basename', glob("$folder/cache/modules/*/*") ?: []);
        return array_values(array_filter($files, static fn (string $name): bool => !str_contains($name, '.')));
    }
}
