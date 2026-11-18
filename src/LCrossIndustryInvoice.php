<?php

declare(strict_types=1);

namespace Lpdf;

use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\EngineRunner;
use Lpdf\Engine\ExtractInvoiceDataOptions;
use Lpdf\Engine\GeneratePdfOptions;
use Lpdf\Engine\InvoiceFormat;
use Lpdf\Kit\PdfDocument;

/**
 * A Factur-X / ZUGFeRD invoice PDF made from the invoice's XML, the EN 16931 Cross Industry Invoice that accounting
 * software writes. The engine reads the XML through a map and checks it; this class hands the values to the caller's
 * `InvoiceFormat` functions.
 *
 * ```php
 * $pdf = LCrossIndustryInvoice::generatePdf(L::engine(), $template, new GeneratePdfOptions(
 *     invoiceXml: file_get_contents('invoice.xml'),
 *     createdOn: gmdate('c'),
 *     format: new InvoiceFormat(date: fn (string $value) => date('d.m.Y', strtotime($value))),
 * ));
 * ```
 */
final class LCrossIndustryInvoice
{
    private function __construct() {}

    /**
     * The invoice's PDF, a Factur-X / ZUGFeRD file: a template, an XML string, filled with the values of the invoice,
     * which it binds under the map's prefix (`invoice:seller.name`) beside the options' data, or a document built in
     * code, which has no bindings. The file is PDF/A-3, 3b unless the document sets 3a or 3u, and carries the XML as
     * `factur-x.xml`, or `xrechnung.xml` for XRECHNUNG. The document says only how the invoice looks: it declares no
     * invoice file.
     *
     * @param  PdfEngine          $engine              The engine, which carries the fonts, the license key and the limits.
     *                                                 It reads the invoice too, through its runner and under its
     *                                                 timeout and memory cap, as it renders.
     * @param  string|PdfDocument $templateOrDocument  How the invoice looks: lpdf XML, or a document built with `L`.
     * @return string                                  The bytes of the PDF.
     * @throws EngineException For what `extractInvoiceData` refuses, a template path under the prefix that the map
     *                         lacks, a PDF/A level other than 3a, 3b or 3u, `encrypt`, `pdfx`, and a document that
     *                         declares the invoice file.
     */
    public static function generatePdf(PdfEngine $engine, string|PdfDocument $templateOrDocument, GeneratePdfOptions $options): string
    {
        $invoice = ['xml' => base64_encode($options->invoiceXml)];
        if (!$templateOrDocument instanceof PdfDocument) {
            $invoice['map'] = $options->invoiceMap;
            $invoice['data'] = self::read($engine->invoiceRunner(), $options->invoiceXml, $options->invoiceMap, $options->format);
        }
        return $engine->renderInvoice($templateOrDocument, $invoice, $options->data, $options->createdOn);
    }

    /**
     * The values of an invoice as plain data: a value is a field, a group an array by name, a list a list, all
     * strings, as the map names them. A value the invoice lacks is absent, and a list with no items is empty. For code
     * that needs the values itself: a document built in code, choosing a template, naming the file.
     *
     * It uses no engine: it reads no fonts and no key, under the default time and memory limits.
     *
     * @param  string $invoiceXml  The invoice's XML, an EN 16931 Cross Industry Invoice.
     * @return array<string, mixed>
     * @throws EngineException When the map is not one or doesn't fit the schema, the invoice is not a Cross Industry
     *                         Invoice or is MINIMUM or BASIC WL, or lacks a value the map marks required.
     */
    public static function extractInvoiceData(string $invoiceXml, ?ExtractInvoiceDataOptions $options = null): array
    {
        return self::read((new EngineOptions())->engineRunner(), $invoiceXml, $options?->invoiceMap, $options?->format);
    }

    /** Lpdf's default invoice map, as XML text: every value EN 16931 numbers, the start of a map of your own. */
    public static function defaultInvoiceMap(): string
    {
        $answer = (new EngineOptions())->engineRunner()->invoke(['method' => 'default_invoice_map']);
        if (!isset($answer['map']) || !is_string($answer['map'])) {
            throw new EngineException('Unexpected response from the engine.');
        }
        return $answer['map'];
    }

    /**
     * The values of an invoice, read by the engine through `$runner`, with the format function of each typed one
     * applied.
     *
     * @return array<string, mixed>
     */
    private static function read(EngineRunner $runner, string $invoiceXml, ?string $invoiceMap, ?InvoiceFormat $format): array
    {
        $answer = $runner->invoke([
            'method'      => 'extract_invoice_data',
            'invoice_xml' => base64_encode($invoiceXml),
            'invoice_map' => $invoiceMap,
        ]);
        $data = $answer['data'];
        foreach ($answer['typed'] as $typed) {
            $fn = match ($typed['type']) {
                'date'   => $format?->date,
                'amount' => $format?->amount,
                'code'   => $format?->code,
                default  => null,
            };
            if ($fn === null) {
                continue;
            }
            $holder = &$data;
            foreach ($typed['at'] as $step) {
                $holder = &$holder[$step];
            }
            $holder = (string) $fn($holder, $typed['name']);
            unset($holder);
        }
        return $data;
    }
}
