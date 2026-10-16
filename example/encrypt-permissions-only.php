<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * encrypt-permissions-only.php — render showcase-encryption.xml with RC4-128 encryption,
 * no open password, print and copy disabled.
 *
 * Run from lpdf's root (after 'make build-sdk-php'):
 *   make example-php
 *
 * Output: tmp/sdk-examples/encrypt-permissions-only-php.pdf in lpdf's root.
 */

use Lpdf\L;

$lpdf    = dirname(__DIR__, 3);
$xmlFile = "$lpdf/test/fixtures/showcase-encryption.xml";
$output  = "$lpdf/tmp/sdk-examples";
$outputFile = 'encrypt-permissions-only-php.pdf';

$xml = file_get_contents($xmlFile);

$engine = L::engine();  // no key → renders with the attribution line

// Permissions only — no open password.
// File opens freely; cooperative viewers enforce print: false, copy: false.
$engine->setEncryption('', 's3cr3t', ['print' => false, 'copy' => false]);

$pdf = $engine->render($xml);

if (!is_dir($output)) {
    mkdir($output, 0777, true);
}
file_put_contents("$output/$outputFile", $pdf);
echo "output: $outputFile (" . number_format(strlen($pdf)) . " bytes)\n";
