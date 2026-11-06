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

A render stops with an `EngineException` after 5 seconds; `L::engine(new EngineOptions(timeout: 60))` sets another limit in seconds, and `timeout: 0` sets none. It also stops once it needs more than 1024 MB of memory; `maxMemoryMb: 2048` sets another cap, from 1 to 4096 MB.

## Requirements

- PHP 8.2+ with the DOM extension (`ext-dom`; on Debian and Ubuntu it is the `php-xml` package)
- One of the two ways the SDK runs the engine, which it picks itself:
  - **The native library** in the package, through PHP's FFI extension (`ext-ffi`): on Linux with glibc 2.17 or newer, on x86_64 or arm64. A web server needs `ffi.enable=true` in php.ini; the command line allows FFI as it is.
  - **[`wasmtime`](https://wasmtime.dev)** on `PATH`, which runs the engine's WebAssembly: everywhere else, such as macOS, Windows and Alpine, and where FFI isn't allowed. Where PHP's user can't write its home, as `www-data` under PHP-FPM, it also needs PHP's POSIX extension (`ext-posix`), for the SDK's cache folder (*How PHP runs the engine*).

## How PHP runs the engine

`L::engine()` renders with the native library where it loads, and with `wasmtime` otherwise. `$engine->runner()` says which one renders: `ffi` or `wasm`.

| | Native library (`ffi`) | wasmtime (`wasm`) |
|---|---|---|
| Runs on | Linux with glibc 2.17 or newer, x86_64 and arm64 | Wherever wasmtime runs |
| Needs | The FFI extension, and `ffi.enable=true` under a web server | The `wasmtime` program on `PATH` |
| Each render | A call in PHP's own process | A process of its own, which adds about 20 ms |
| Sandbox | None: the engine has the rights of the PHP process | WebAssembly's: no files, no network |

`runner` on `EngineOptions` picks how: `'auto'`, the default, chooses as above; `'ffi'` renders with the native library only, and throws an `EngineException` that names what stands in the way where it can't load; `'wasm'` renders with `wasmtime` only. For an app that renders templates, fonts or images its own users upload, `wasm` keeps the engine in WebAssembly's sandbox. `libPath` loads the native library from another path.

`wasmtime` compiles the engine to native code at the first render and keeps it for the renders after, in a folder of the SDK's: `lpdf-wasmtime-<uid>` in PHP's temp directory, `sys_get_temp_dir()`, made with mode 0700 where it is missing. This way the SDK renders under PHP-FPM too, where PHP runs as `www-data`, whose home wasmtime can't write. `wasmCacheDir` on `EngineOptions` sets another folder. wasmtime runs what the folder holds without checking it, so the SDK uses a folder only where PHP's user owns it and group and others can't open it, which it tells through PHP's POSIX extension (`ext-posix`). Most PHP builds have the extension; Alpine packages it apart, such as `php83-posix`. Where the SDK uses no folder, wasmtime keeps the engine in its own default place, in the user's home, and where it can't create that either, the render throws an `EngineException` that says why and what to set: `wasmCacheDir`, or the POSIX extension where it is missing.

The timeout and the memory cap hold through both. The engine's memory is outside PHP's `memory_limit` either way. Through the native library, a fault the engine can't catch, such as running out of memory before its cap stops it, ends PHP's process; through `wasmtime` it ends the `wasmtime` process, and the render throws an `EngineException`. Where a render could need more memory than the machine has, keep `maxMemoryMb` below it, or render with `wasm`. Through the native library the engine stops a render at its timeout itself, and a step it hands to a library, such as reading the XML, subsetting a font or compressing an image, finishes first.

## Docs

[lpdf.io/docs](https://lpdf.io/docs/install/?sdk=php&utm_campaign=sdk-php&utm_medium=referral&utm_source=readme)

## Issues

Report bugs and request features at [github.com/lpdfio/lpdf/issues](https://github.com/lpdfio/lpdf/issues), the one tracker for the engine, the SDKs and the VS Code extension. Pull requests are not accepted.

--

Dual-licensed: Community License (free) and Commercial License (paid). See [LICENSE](LICENSE) for full terms.
