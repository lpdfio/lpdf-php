<?php

declare(strict_types=1);

// A PHP-FPM request for RunnerTest: the user PHP runs as, which runner `auto` picks under FPM, what `runner: 'ffi'`
// says there, the hash of bench_xs rendered with `auto`, and the engines wasmtime keeps compiled in the SDK's folder
// after it, as JSON.

require \dirname(__DIR__, 2) . '/vendor/autoload.php';

use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\L;
use Lpdf\Tests\SnapshotHelper;

header('Content-Type: application/json');
$answer = [
    'user' => posix_getpwuid(posix_geteuid())['name'] ?? null,
    'ffi.enable' => ini_get('ffi.enable'),
];
try {
    L::engine(new EngineOptions(runner: 'ffi'))->runner();
    $answer['ffiRefused'] = null;
} catch (EngineException $e) {
    $answer['ffiRefused'] = $e->getMessage();
}
try {
    $engine = L::engine()->setLicenseKey('test-key');
    $answer['runner'] = $engine->runner();
    $answer['hash'] = hash('sha256', $engine->render((string) file_get_contents(SnapshotHelper::fixtures() . '/bench_xs.xml')));
} catch (EngineException $e) {
    $answer['error'] = $e->getMessage();
}
// A compiled engine is a file of wasmtime's cache with no dot in its name: the `.stats` and `.wip-` files beside it are
// wasmtime's bookkeeping.
$compiled = glob(sys_get_temp_dir() . '/lpdf-wasmtime-' . posix_geteuid() . '/cache/modules/*/*') ?: [];
$answer['compiled'] = array_values(array_filter(array_map('basename', $compiled), static fn ($name) => !str_contains($name, '.')));
echo json_encode($answer, JSON_THROW_ON_ERROR);
