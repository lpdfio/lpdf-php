<?php

declare(strict_types=1);

// The render times of bench_xs and bench_l through each runner, measured back to back: one render each way to warm
// up, then 21 each way, alternating, and the median of each. Prints a Markdown table, for the summary of a CI run.
//
//   php test/bench-runners.php

require \dirname(__DIR__) . '/vendor/autoload.php';

use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\Runner;
use Lpdf\L;
use Lpdf\Tests\SnapshotHelper;

const RUNS = 21;

$engines = [
    Runner::Ffi => L::engine(new EngineOptions(runner: Runner::Ffi))->setLicenseKey('test-key'),
    Runner::Wasm => L::engine(new EngineOptions(runner: Runner::Wasm))->setLicenseKey('test-key'),
];

/** The milliseconds one render of `$xml` takes. */
function render_ms(\Lpdf\PdfEngine $engine, string $xml): float
{
    $start = hrtime(true);
    $engine->render($xml);
    return (hrtime(true) - $start) / 1e6;
}

/** @param list<float> $times */
function median(array $times): float
{
    sort($times);
    return $times[intdiv(count($times), 2)];
}

$cpu = php_uname('m');
echo "### Render times on $cpu, PHP " . PHP_VERSION . ", median of " . RUNS . " renders in ms\n\n";
echo "| Fixture | FFI | wasmtime | wasmtime / FFI |\n|---|---|---|---|\n";
foreach (['bench_xs', 'bench_l'] as $fixture) {
    $xml = (string) file_get_contents(SnapshotHelper::fixtures() . "/$fixture.xml");
    $times = [Runner::Ffi => [], Runner::Wasm => []];
    foreach ($engines as $engine) {
        render_ms($engine, $xml);
    }
    for ($run = 0; $run < RUNS; $run++) {
        foreach ($engines as $runner => $engine) {
            $times[$runner][] = render_ms($engine, $xml);
        }
    }
    $ffi = median($times[Runner::Ffi]);
    $wasm = median($times[Runner::Wasm]);
    printf("| `%s` | %.2f | %.1f | %.0f× |\n", $fixture, $ffi, $wasm, $wasm / $ffi);
}
