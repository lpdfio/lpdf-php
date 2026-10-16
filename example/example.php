<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * example.php — render every example in lpdf's examples/ folder.
 *
 * An example is a folder examples/<n>-<id>/ with its document.xml, its document.json if it has data, and the
 * fonts and images its <assets> name, by paths relative to the folder. Each is rendered the way the docs on
 * lpdf.io render it: a fresh engine, those fonts and images loaded, and the data passed to render.
 *
 * Run from lpdf's root (after 'make build-sdk-php'):
 *   make example-php
 *
 * Output: tmp/sdk-examples/<n>-<id>-php.pdf in lpdf's root.
 */

use Lpdf\Engine\RenderOptions;
use Lpdf\L;

// lpdf's root: /app in make's container, which mounts examples/ and tmp/sdk-examples/ there
$lpdf     = dirname(__DIR__, 3);
$examples = "$lpdf/examples";
$output   = "$lpdf/tmp/sdk-examples";
if (!is_dir($output)) {
    mkdir($output, 0777, true);
}

$folders = array_filter(glob("$examples/*", GLOB_ONLYDIR), fn (string $folder) => preg_match('/^\d+-/', basename($folder)) === 1);
usort($folders, fn (string $a, string $b) => (int) basename($a) <=> (int) basename($b));

foreach ($folders as $folder) {
    $name     = basename($folder);
    $xml      = file_get_contents("$folder/document.xml");
    $dataFile = "$folder/document.json";
    $data     = is_file($dataFile) ? json_decode(file_get_contents($dataFile), associative: true) : null;

    // init engine: a fresh one for each example, so a font one of them names does not reach the next
    $engine = L::engine();  // no key → renders with the attribution line

    // load the fonts and images the document names; its comments may name others, to show the format
    $body = preg_replace('/<!--.*?-->/s', '', $xml);
    preg_match_all('/<font\s+name="([^"]+)"\s+src="([^"]+)"/', $body, $fonts, PREG_SET_ORDER);
    foreach ($fonts as [, $font, $src]) {
        $engine->loadFont($font, file_get_contents("$folder/$src"));
    }
    preg_match_all('/<image\s+name="([^"]+)"\s+src="([^"]+)"/', $body, $images, PREG_SET_ORDER);
    foreach ($images as [, $image, $src]) {
        $engine->loadImage($image, file_get_contents("$folder/$src"));
    }

    // render pdf from xml, with the example's data if it has any
    $pdf = $engine->render($xml, $data === null ? null : new RenderOptions(data: $data));

    // write pdf to output file
    $outputFile = "{$name}-php.pdf";
    file_put_contents("$output/$outputFile", $pdf);

    echo "output: $outputFile (" . number_format(strlen($pdf)) . " bytes)\n";
}
