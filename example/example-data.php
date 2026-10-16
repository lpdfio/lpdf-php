<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * example-data.php — render the invoice in lpdf's examples/ with its data from document.json.
 *
 * Run from lpdf's root (after 'make build-sdk-php'):
 *   make example-php
 *
 * Output: tmp/sdk-examples/example-data-php.pdf in lpdf's root.
 */

use Lpdf\L;
use Lpdf\Engine\RenderOptions;

$lpdf       = dirname(__DIR__, 3);
$xmlFile    = "$lpdf/examples/2-invoice/document.xml";
$jsonFile   = "$lpdf/examples/2-invoice/document.json";
$output     = "$lpdf/tmp/sdk-examples";
$outputFile = 'example-data-php.pdf';

$xml  = file_get_contents($xmlFile);
$data = json_decode(file_get_contents($jsonFile), associative: true);

$engine = L::engine();  // no key → renders with the attribution line

$pdf = $engine->render($xml, new RenderOptions(data: $data));

if (!is_dir($output)) {
    mkdir($output, 0777, true);
}
file_put_contents("$output/$outputFile", $pdf);
echo "output: $outputFile (" . number_format(strlen($pdf)) . " bytes)\n";
