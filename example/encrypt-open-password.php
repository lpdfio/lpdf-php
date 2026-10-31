<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * encrypt-open-password.php — render showcase-encryption.xml, whose <output> asks for AES-256
 * and allows neither copying nor modifying, with an open password.
 *
 * Run from lpdf's root (after 'make build-sdk-php'):
 *   make example-php
 *
 * Output: tmp/sdk-examples/encrypt-open-password-php.pdf in lpdf's root.
 */

use Lpdf\Engine\RenderOptions;
use Lpdf\L;

$lpdf    = dirname(__DIR__, 3);
$xmlFile = "$lpdf/test/fixtures/showcase-encryption.xml";
$output  = "$lpdf/tmp/sdk-examples";
$outputFile = 'encrypt-open-password-php.pdf';

$xml = file_get_contents($xmlFile);

$engine = L::engine();  // no key → renders with the attribution line

// With open password — viewers prompt for 'password' before displaying content.
// The cipher and the permissions come from the document's <output>, the passwords from here.
$pdf = $engine->render($xml, new RenderOptions(userPassword: 'password', ownerPassword: 'owner'));

if (!is_dir($output)) {
    mkdir($output, 0777, true);
}
file_put_contents("$output/$outputFile", $pdf);
echo "output: $outputFile (" . number_format(strlen($pdf)) . " bytes)\n";
