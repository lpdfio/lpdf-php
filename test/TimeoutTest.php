<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\RenderOptions;
use Lpdf\Engine\Runner;
use Lpdf\L;
use Lpdf\PdfEngine;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The timeout. Through `wasmtime`, its process is ended at the deadline: a render that runs long is a small WebAssembly
 * module of the test's own, in the text format, which `wasmtime` runs in place of the engine, since the real engine
 * renders the largest fixture in well under a second. Through the native library, the engine stops the render itself:
 * a render that runs long is a big document.
 */
final class TimeoutTest extends TestCase
{
    private const SPIN_FOREVER = '(module (func (export "_start") (loop $spin (br $spin))))';

    // Spins on the monotonic clock for two seconds, then writes a response that holds the bytes "%PDF-".
    private const SPIN_TWO_SECONDS = <<<'WAT'
        (module
          (import "wasi_snapshot_preview1" "clock_time_get" (func $clock (param i32 i64 i32) (result i32)))
          (import "wasi_snapshot_preview1" "fd_write" (func $write (param i32 i32 i32 i32) (result i32)))
          (memory (export "memory") 1)
          (data (i32.const 32) "{\"pdf\": \"JVBERi0=\"}")
          (func (export "_start")
            (local $until i64)
            (drop (call $clock (i32.const 1) (i64.const 1) (i32.const 0)))
            (local.set $until (i64.add (i64.load (i32.const 0)) (i64.const 2000000000)))
            (loop $spin
              (drop (call $clock (i32.const 1) (i64.const 1) (i32.const 0)))
              (br_if $spin (i64.lt_u (i64.load (i32.const 0)) (local.get $until))))
            (i32.store (i32.const 16) (i32.const 32))
            (i32.store (i32.const 20) (i32.const 19))
            (drop (call $write (i32.const 1) (i32.const 16) (i32.const 1) (i32.const 24)))))
        WAT;

    private const MINIMAL =
        '<lpdf version="1"><document><section><layout><text>x</text></layout></section></document></lpdf>';

    /** @var list<string> */
    private array $standIns = [];

    protected function tearDown(): void
    {
        foreach ($this->standIns as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testARenderPastItsTimeoutThrowsNamingTheLimit(): void
    {
        [$seconds, $error] = self::timedRender($this->standIn(self::SPIN_TWO_SECONDS, 1));

        $this->assertSame('The render timed out after 1 second.', $error?->getMessage());
        $this->assertGreaterThanOrEqual(1, $seconds);
        $this->assertLessThan(1.5, $seconds);
    }

    public function testARenderWithATimeoutOf0RunsWithNoLimit(): void
    {
        $start = hrtime(true);
        $pdf   = $this->standIn(self::SPIN_TWO_SECONDS, 0)->render(self::MINIMAL);

        $this->assertSame('%PDF-', $pdf);
        $this->assertGreaterThanOrEqual(2, (hrtime(true) - $start) / 1e9);
    }

    public function testANegativeTimeoutIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('timeout must be a whole number of seconds, 0 for no limit, not -1');

        new EngineOptions(timeout: -1);
    }

    public function testARenderStopsAfter5SecondsWhenTheTimeoutIsLeftOut(): void
    {
        [$seconds, $error] = self::timedRender($this->standIn(self::SPIN_FOREVER, null));

        $this->assertSame('The render timed out after 5 seconds.', $error?->getMessage());
        $this->assertGreaterThanOrEqual(5, $seconds);
        $this->assertLessThan(5.5, $seconds);
    }

    public function testTheNextRenderAfterATimeoutGivesTheRightHash(): void
    {
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
        [, $error] = self::timedRender($this->standIn(self::SPIN_FOREVER, 1));
        $this->assertInstanceOf(EngineException::class, $error);

        SnapshotHelper::compareOrUpdate('bench_m', self::renderFixture('bench_m', new EngineOptions(runner: Runner::Wasm)));
    }

    public function testARenderThroughTheNativeLibraryPastItsTimeoutThrowsAndTheNextGivesTheRightHash(): void
    {
        RunnerSupport::need(Runner::Ffi);
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
        $engine = L::engine(new EngineOptions(runner: Runner::Ffi, timeout: 1, maxMemoryMb: 4096))->setLicenseKey('test-key');
        // Two million lines, a page at a time: about 40,000 pages, which take the native library several seconds.
        $xml = '<lpdf version="1"><document><section><layout><column bulk="true"><text data-source="rows">'
            . 'A line of a long report, with words enough to fill the width of a line or two of the page.'
            . '</text></column></layout></section></document></lpdf>';
        $data = new RenderOptions(data: ['rows' => range(1, 2_000_000)]);

        $start = hrtime(true);
        try {
            $engine->render($xml, $data);
            $this->fail('the render ran past its timeout');
        } catch (EngineException $e) {
            $seconds = (hrtime(true) - $start) / 1e9;
            $this->assertSame('The render timed out after 1 second.', $e->getMessage());
        }
        // What the render held is freed as it stops, which takes a moment for thousands of pages.
        $this->assertGreaterThanOrEqual(1, $seconds);
        $this->assertLessThan(2, $seconds);

        SnapshotHelper::compareOrUpdate('bench_m', $engine->render(self::fixture('bench_m'), new RenderOptions(skipEncryption: true)));
    }

    #[DataProvider('runners')]
    public function testAFixtureRendersWithATimeoutOf0(string $runner): void
    {
        RunnerSupport::need($runner);
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }

        SnapshotHelper::compareOrUpdate('bench_l', self::renderFixture('bench_l', new EngineOptions(timeout: 0, runner: $runner)));
    }

    /** @return array<string, array{string}> */
    public static function runners(): array
    {
        return RunnerSupport::runners();
    }

    /** An engine that runs the module `$wat` with wasmtime in place of the engine's binary. */
    private function standIn(string $wat, ?int $timeout): PdfEngine
    {
        RunnerSupport::need(Runner::Wasm);
        $path = tempnam(sys_get_temp_dir(), 'lpdf-stand-in-');
        $this->assertIsString($path);
        file_put_contents($path, $wat);
        $this->standIns[] = $path;
        return L::engine(new EngineOptions(wasmBinary: $path, timeout: $timeout, runner: Runner::Wasm));
    }

    /** @return array{float, ?EngineException} The seconds the render took, and its error if it threw one. */
    private static function timedRender(PdfEngine $engine): array
    {
        $start = hrtime(true);
        try {
            $engine->render(self::MINIMAL);
            return [(hrtime(true) - $start) / 1e9, null];
        } catch (EngineException $e) {
            return [(hrtime(true) - $start) / 1e9, $e];
        }
    }

    private static function renderFixture(string $name, EngineOptions $options): string
    {
        return L::engine($options)->setLicenseKey('test-key')->render(self::fixture($name), new RenderOptions(skipEncryption: true));
    }

    private static function fixture(string $name): string
    {
        return (string) file_get_contents(SnapshotHelper::fixtures() . "/$name.xml");
    }
}
