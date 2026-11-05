<?php

declare(strict_types=1);

// A PHP-FPM request for RunnerTest: which runner `auto` picks under FPM, what `runner: 'ffi'` says there, and the
// hash of bench_xs rendered with `auto`, as JSON.

require \dirname(__DIR__, 2) . '/vendor/autoload.php';

use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\L;
use Lpdf\Tests\SnapshotHelper;

header('Content-Type: application/json');
$answer = ['ffi.enable' => ini_get('ffi.enable')];
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
echo json_encode($answer, JSON_THROW_ON_ERROR);
