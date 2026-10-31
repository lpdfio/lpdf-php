<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * encrypt-permissions-only.php — render showcase-encryption.xml, whose <output> asks for AES-256
 * and allows neither copying nor modifying, with no open password.
 *
 * Run from lpdf's root (after 'make build-sdk-php'):
 *   make example-php
 *
 * Output: tmp/sdk-examples/encrypt-permissions-only-php.pdf in lpdf's root.
 */

use Lpdf\Engine\RenderOptions;
use Lpdf\L;

$lpdf    = dirname(__DIR__, 3);
$xmlFile = "$lpdf/test/fixtures/showcase-encryption.xml";
$output  = "$lpdf/tmp/sdk-examples";
$outputFile = 'encrypt-permissions-only-php.pdf';

$xml = file_get_contents($xmlFile);

$engine = L::engine();  // no key → renders with the attribution line

// Permissions only — no open password.
// File opens freely; cooperative viewers enforce the allow of the document's <output>.
$pdf = $engine->render($xml, new RenderOptions(ownerPassword: 's3cr3t'));

if (!is_dir($output)) {
    mkdir($output, 0777, true);
}
file_put_contents("$output/$outputFile", $pdf);
echo "output: $outputFile (" . number_format(strlen($pdf)) . " bytes)\n";
