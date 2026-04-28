# lpdfio/lpdf

PHP adapter for [Lpdf](https://lpdf.io) — an accurate, efficient, and cross-platform PDF engine.

## Installation

```bash
composer require lpdfio/lpdf
```

## Usage

```php
<?php

use Lpdf\LpdfEngine;

$engine = new LpdfEngine('');

$engine->loadFont('montserrat', file_get_contents('fonts/Montserrat-Regular.ttf'));
$engine->loadImage('logo', file_get_contents('images/logo.png'));

$xml = file_get_contents('document.xml');
$pdf = $engine->renderPdf($xml);

file_put_contents('output.pdf', $pdf);
```

## XML format

Documents are defined in a layout XML format. See the [Lpdf documentation](https://lpdf.io/docs) and [examples](https://github.com/lpdfio/lpdf/tree/main/docs/examples) for the full schema.

```xml
<stack spacing="m" padding="l">
  <text font-size="xl" font="Montserrat-Bold">Invoice #1001</text>
  <grid columns="2">
    <text>Date</text>      <text>2026-04-25</text>
    <text>Due</text>       <text>2026-05-25</text>
  </grid>
</stack>
```

## Requirements

- PHP 8.2+
- A WASI-capable runtime is bundled — no additional extensions required.

## License

Dual-licensed: Community License (free) and Commercial License (paid). See [LICENSE](LICENSE) for full terms.
