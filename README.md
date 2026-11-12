<img src="https://raw.githubusercontent.com/lpdfio/lpdf-php/main/lpdf-mark.svg" height="48" alt="Lpdf - PDF as Code" />

# lpdfio/lpdf

`lpdfio/lpdf` is the PHP SDK for [Lpdf](https://lpdf.io?utm_campaign=sdk-php&utm_medium=referral&utm_source=readme), the PDF engine that renders a document written in XML or built in code to the same PDF in every language.

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

## Requirements

- PHP 8.2 or later, with the DOM extension (`ext-dom`; on Debian and Ubuntu it is the `php-xml` package)
- One of the two ways the SDK runs the engine, which it picks itself ([How PHP runs the engine](https://lpdf.io/docs/install/?sdk=php&utm_campaign=sdk-php&utm_medium=referral&utm_source=readme#how-php-runs-the-engine)):
  - The native library in the package, through PHP's FFI extension (`ext-ffi`): on Linux with glibc 2.17 or newer, on x86_64 or arm64. A web server needs `ffi.enable=true` in php.ini; the command line allows FFI as it is.
  - The [`wasmtime`](https://wasmtime.dev) command-line tool on your `PATH`, which runs the bundled WASI binary: everywhere else, such as macOS, Windows and Alpine, and where FFI isn't allowed. On Linux and macOS, where PHP's user can't write its home, as `www-data` under PHP-FPM, it also needs PHP's POSIX extension (`ext-posix`), for the SDK's cache folder.

## Docs

[lpdf.io/docs](https://lpdf.io/docs/install/?sdk=php&utm_campaign=sdk-php&utm_medium=referral&utm_source=readme)

## Issues

Report bugs and request features at [github.com/lpdfio/lpdf/issues](https://github.com/lpdfio/lpdf/issues), the one tracker for the engine, the SDKs and the VS Code extension. Pull requests are not accepted.

---

Licensed under the Lpdf License. See [LICENSE](LICENSE) for full terms.
