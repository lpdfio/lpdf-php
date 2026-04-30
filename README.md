<p align="center"><img src="lpdf-light.png" height="48" alt="Lpdf"></p>

# lpdfio/lpdf

**PHP SDK for [Lpdf](https://lpdf.io) — PDF as Code on every platform**

You describe a document as code or XML. Lpdf renders a compact, pixel-perfect PDF — identical across platforms.

## Installation

```bash
composer require lpdfio/lpdf
```

## Usage

```php
use Lpdf\L;
use const Lpdf\NoAttr;

$engine = L::engine();

$doc = L::document(new DocumentAttr(size: 'letter', margin: '48pt'), [
    L::section(NoAttr, [
        L::layout(NoAttr, [
            L::stack(new StackAttr(gap: '24pt'), [
                L::split(NoAttr, [
                    L::text(new TextAttr(fontSize: '8pt', color: '#888888'), ['ACME CORP']),
                    L::text(new TextAttr(fontSize: '22pt', bold: 'true'), ['Project Proposal']),
                ]),
                L::divider(new DividerAttr(thickness: 'xs')),
                L::text(new TextAttr(fontSize: '13pt', bold: 'true'), ['Scope of Work']),
                L::flank(new FlankAttr(gap: '12pt', align: 'start'), [
                    L::text(new TextAttr(color: '#888888', width: '24pt'), ['01']),
                    L::text(NoAttr, ['Discovery & Research']),
                ]),
            ]),
        ]),
    ]),
]);

$pdf = $engine->render($doc);
```

## Requirements

- PHP 8.2+
- [`wasmtime`](https://wasmtime.dev) CLI must be available in `PATH` (used to run the bundled WASI binary).

## Docs

[lpdf.io/docs/php](https://lpdf.io/docs/php)

--

Dual-licensed: Community License (free) and Commercial License (paid). See [LICENSE](LICENSE) for full terms.
