<img src="https://raw.githubusercontent.com/lpdfio/lpdf-php/main/lpdf-mark.svg" height="48" alt="Lpdf - PDF as Code" />

# lpdfio/lpdf

**PHP SDK for [Lpdf](https://lpdf.io?utm_campaign=sdk-php&utm_medium=referral&utm_source=readme) — PDF as Code on every platform**

You describe a document as code or XML. Lpdf renders a compact, pixel-perfect PDF — identical across platforms.

## Installation

```bash
composer require lpdfio/lpdf
```

## Usage

```php
use Lpdf\L;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\LayoutAttr;
use Lpdf\Layout\ColumnAttr;
use Lpdf\Layout\DividerAttr;
use Lpdf\Layout\RowAttr;
use Lpdf\Layout\TextAttr;
use const Lpdf\NoAttr;

$engine = L::engine();

$doc = L::document(new DocumentAttr(size: 'letter'), [
    L::section(NoAttr, [
        L::layout(new LayoutAttr(margin: '48pt'), [
            L::column(new ColumnAttr(gap: '24pt'), [
                L::row(new RowAttr(justify: 'between'), [
                    L::text(new TextAttr(fontSize: '8pt', color: '#888888'), ['ACME CORP']),
                    L::text(new TextAttr(fontSize: '22pt', bold: true), ['Project Proposal']),
                ]),
                L::divider(new DividerAttr(thickness: 'xs')),
                L::text(new TextAttr(fontSize: '13pt', bold: true), ['Scope of Work']),
                L::row(new RowAttr(gap: '12pt', align: 'start'), [
                    L::text(new TextAttr(color: '#888888', width: '24pt'), ['01']),
                    L::text(new TextAttr(width: 'fill'), ['Discovery & Research']),
                ]),
            ]),
        ]),
    ]),
]);

$pdf = $engine->render($doc);
```

An attribute class takes its attributes as named arguments, `new TextAttr(fontSize: 'xl')`. Positional arguments aren't supported: the parameters follow the schema's order, so an attribute added in a later version can move them.

## Requirements

- PHP 8.2+ with the DOM extension (`ext-dom`; on Debian and Ubuntu it is the `php-xml` package)
- [`wasmtime`](https://wasmtime.dev) CLI must be available in `PATH` (used to run the bundled WASI binary).

## Docs

[lpdf.io/docs](https://lpdf.io/docs/install/?sdk=php&utm_campaign=sdk-php&utm_medium=referral&utm_source=readme)

## Issues

Report bugs and request features at [github.com/lpdfio/lpdf/issues](https://github.com/lpdfio/lpdf/issues), the one tracker for the engine, the SDKs and the VS Code extension. Pull requests are not accepted.

--

Dual-licensed: Community License (free) and Commercial License (paid). See [LICENSE](LICENSE) for full terms.
