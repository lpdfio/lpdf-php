<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\EngineException;
use Lpdf\Engine\ExtractInvoiceDataOptions;
use Lpdf\Engine\GeneratePdfOptions;
use Lpdf\Engine\InvoiceFormat;
use Lpdf\Kit\DocumentAssets;
use Lpdf\Kit\DocumentAttr;
use Lpdf\Kit\DocumentMeta;
use Lpdf\Kit\FontAttr;
use Lpdf\Kit\LayoutAttr;
use Lpdf\Kit\PdfDocument;
use Lpdf\L;
use Lpdf\LCrossIndustryInvoice;
use Lpdf\Layout\ColumnAttr;
use Lpdf\Layout\DividerAttr;
use Lpdf\Layout\RowAttr;
use Lpdf\Layout\TableAttr;
use Lpdf\Layout\TextAttr;
use Lpdf\PdfEngine;
use PHPUnit\Framework\TestCase;

use const Lpdf\NoAttr;

/**
 * LCrossIndustryInvoice: a Factur-X / ZUGFeRD invoice PDF made from the invoice's XML. The template, the invoices, the
 * font and the snapshot hash are core's, its test/fixtures, test/assets and test/snapshots, which CI copies in from the
 * core tag.
 */
final class CrossIndustryInvoiceTest extends TestCase
{
    private const CREATED_ON = '2026-11-18T10:00:00Z';
    private const NOTE = 'Payable within 30 days, by bank transfer.';

    private static function assets(): string
    {
        return dirname(SnapshotHelper::fixtures()) . '/assets';
    }

    private static function invoice(string $name): string
    {
        return (string) file_get_contents(self::assets() . "/files/facturx/$name");
    }

    private static function template(): string
    {
        return (string) file_get_contents(SnapshotHelper::fixtures() . '/showcase-cross-industry-invoice.xml');
    }

    /** An unlicensed engine with the template's font loaded under its name. */
    private static function engine(): PdfEngine
    {
        return L::engine()->loadFont('body', (string) file_get_contents(self::assets() . '/fonts/SourceSans3-Regular.otf'));
    }

    /** How values are written on the page, as the examples write them: PHP's own formatters, and the words for the codes. */
    private static function format(): InvoiceFormat
    {
        $codeNames = [
            'typeCode'   => ['380' => 'Invoice', '381' => 'Credit note'],
            'lines.unit' => ['H87' => 'pcs', 'KGM' => 'kg'],
        ];
        return new InvoiceFormat(
            date: fn (string $value): string => (new \DateTimeImmutable($value))->format('j F Y'),
            amount: fn (string $value): string => number_format((float) $value, 2, '.', ','),
            code: fn (string $value, string $entryName): string => $codeNames[$entryName][$value] ?? $value,
        );
    }

    /**
     * The page of the template, built in code with the values of `extractInvoiceData`, as the second example builds it.
     *
     * @param array<string, mixed> $invoiceData
     */
    private static function documentInCode(array $invoiceData): PdfDocument
    {
        $muted = new TextAttr(fontSize: '9pt', color: '#5b6475');
        $mutedRight = new TextAttr(fontSize: '9pt', color: '#5b6475', align: 'right');
        $right = new TextAttr(align: 'right');
        $title = new TextAttr(fontSize: '16pt', color: '#1763cf');
        $total = new TextAttr(fontSize: '14pt');
        ['seller' => $seller, 'buyer' => $buyer, 'totals' => $totals] = $invoiceData;
        $inTaxCurrency = isset($totals['vatInTaxCurrency']) ? [L::row(new RowAttr(gap: 'xs'), [
            L::text($muted, ['VAT in']), L::text($muted, [$totals['taxCurrency']]), L::text($muted, [$totals['vatInTaxCurrency']]),
        ])] : [];

        return L::document(new DocumentAttr(
            size: 'a4', font: 'body',
            assets: new DocumentAssets(fonts: [new FontAttr(name: 'body', src: '../assets/fonts/SourceSans3-Regular.otf')]),
            meta: new DocumentMeta(title: 'Invoice', author: 'Northwind Paper SAS', lang: 'en'),
        ), [
            L::section(NoAttr, [
                L::layout(new LayoutAttr(margin: '48pt'), [
                    L::column(new ColumnAttr(gap: 'l'), [
                        L::row(new RowAttr(align: 'end'), [
                            L::column(new ColumnAttr(width: 'fill', gap: 'xs'), [
                                L::text(new TextAttr(fontSize: '20pt'), [$seller['name']]),
                                L::text($muted, [$seller['street']]),
                                L::row(new RowAttr(gap: 'xs'), [L::text($muted, [$seller['postcode']]), L::text($muted, [$seller['city']])]),
                                L::row(new RowAttr(gap: 'xs'), [L::text($muted, ['VAT']), L::text($muted, [$seller['vatId']])]),
                            ]),
                            L::row(new RowAttr(gap: 'xs'), [L::text($title, [$invoiceData['typeCode']]), L::text($title, [$invoiceData['number']])]),
                        ]),
                        L::divider(new DividerAttr(color: '#dde3ed', thickness: '0.5pt')),
                        L::row(new RowAttr(gap: 'l'), [
                            L::column(new ColumnAttr(width: 'fill', gap: 'xs'), [
                                L::text($muted, ['Billed to']),
                                L::text(NoAttr, [$buyer['name']]),
                                L::text(NoAttr, [$buyer['street']]),
                                L::row(new RowAttr(gap: 'xs'), [L::text(NoAttr, [$buyer['postcode']]), L::text(NoAttr, [$buyer['city']])]),
                            ]),
                            L::column(new ColumnAttr(width: 'fill', gap: 'xs'), [
                                L::text($muted, ['Issued · Due']),
                                L::text(NoAttr, [$invoiceData['issued']]),
                                ...array_map(fn (array $term) => L::row(NoAttr, [L::text(NoAttr, [$term['due']])]), $invoiceData['paymentTerms']),
                            ]),
                        ]),
                        L::table(new TableAttr(cols: '4fr 1fr 1fr 1fr 1fr 1fr'), [
                            L::thead(NoAttr, [
                                L::td(NoAttr, [L::text($muted, ['Item'])]),
                                L::td(NoAttr, [L::text($mutedRight, ['Qty'])]),
                                L::td(NoAttr, [L::text($mutedRight, ['Unit'])]),
                                L::td(NoAttr, [L::text($mutedRight, ['Price'])]),
                                L::td(NoAttr, [L::text($mutedRight, ['VAT %'])]),
                                L::td(NoAttr, [L::text($mutedRight, ['Amount'])]),
                            ]),
                            ...array_map(fn (array $line) => L::tr(NoAttr, [
                                L::td(NoAttr, [L::text(NoAttr, [$line['name']])]),
                                L::td(NoAttr, [L::text($right, [$line['quantity']])]),
                                L::td(NoAttr, [L::text($right, [$line['unit']])]),
                                L::td(NoAttr, [L::text($right, [$line['unitPrice']])]),
                                L::td(NoAttr, [L::text($right, [$line['vatRate']])]),
                                L::td(NoAttr, [L::text($right, [$line['net']])]),
                            ]), $invoiceData['lines']),
                        ]),
                        L::divider(new DividerAttr(color: '#dde3ed', thickness: '0.5pt')),
                        L::table(new TableAttr(cols: '1fr 1fr 1fr'), [
                            L::thead(NoAttr, [
                                L::td(NoAttr, [L::text($muted, ['VAT rate (%)'])]),
                                L::td(NoAttr, [L::text($mutedRight, ['Basis'])]),
                                L::td(NoAttr, [L::text($mutedRight, ['VAT'])]),
                            ]),
                            ...array_map(fn (array $tax) => L::tr(NoAttr, [
                                L::td(NoAttr, [L::text(NoAttr, [$tax['rate']])]),
                                L::td(NoAttr, [L::text($right, [$tax['basis']])]),
                                L::td(NoAttr, [L::text($right, [$tax['amount']])]),
                            ]), $invoiceData['taxes']),
                        ]),
                        L::column(new ColumnAttr(gap: 'xs', align: 'end'), [
                            L::row(new RowAttr(gap: 'xs'), [L::text(NoAttr, ['VAT']), L::text(NoAttr, [$totals['vat']]), L::text(NoAttr, [$totals['currency']])]),
                            ...$inTaxCurrency,
                            L::row(new RowAttr(gap: 'xs'), [L::text($total, ['Total']), L::text($total, [$totals['gross']]), L::text($total, [$totals['currency']])]),
                        ]),
                        L::text($muted, [self::NOTE]),
                    ]),
                ]),
            ]),
        ]);
    }

    public function testFillsTheTemplateWithTheInvoiceAndGivesThePdfCoreGives(): void
    {
        $pdf = LCrossIndustryInvoice::generatePdf(self::engine(), self::template(), new GeneratePdfOptions(
            invoiceXml: self::invoice('en16931.xml'), createdOn: self::CREATED_ON, data: ['note' => self::NOTE],
        ));
        $this->assertSame(trim((string) file_get_contents(dirname(SnapshotHelper::fixtures()) . '/snapshots/showcase-cross-industry-invoice.pdf.sha256')), hash('sha256', $pdf));
        $this->assertStringContainsString('<pdfaid:part>3</pdfaid:part>', $pdf);
        $this->assertStringContainsString('<fx:DocumentFileName>factur-x.xml</fx:DocumentFileName>', $pdf);
    }

    public function testGivesTheSamePageFromTheTemplateAndFromTheDocumentBuiltInCode(): void
    {
        $invoiceXml = self::invoice('en16931.xml');
        $engine = self::engine();
        $fromTemplate = LCrossIndustryInvoice::generatePdf($engine, self::template(), new GeneratePdfOptions(
            invoiceXml: $invoiceXml, createdOn: self::CREATED_ON, format: self::format(), data: ['note' => self::NOTE],
        ));
        $invoiceData = LCrossIndustryInvoice::extractInvoiceData($invoiceXml, new ExtractInvoiceDataOptions(format: self::format()));
        $fromCode = LCrossIndustryInvoice::generatePdf($engine, self::documentInCode($invoiceData), new GeneratePdfOptions(
            invoiceXml: $invoiceXml, createdOn: self::CREATED_ON,
        ));
        $this->assertSame(hash('sha256', $fromTemplate), hash('sha256', $fromCode));
    }

    public function testExtractsTheValuesCoreReadsAndFormatsThemByTypeAndEntryName(): void
    {
        $expected = json_decode((string) file_get_contents(self::assets() . '/files/facturx/case-vat-rates.json'), true);
        $this->assertSame($expected, LCrossIndustryInvoice::extractInvoiceData(self::invoice('case-vat-rates.xml')));
        $formatted = LCrossIndustryInvoice::extractInvoiceData(self::invoice('en16931.xml'), new ExtractInvoiceDataOptions(format: self::format()));
        $this->assertSame('31 October 2026', $formatted['issued']);
        $this->assertSame('Invoice', $formatted['typeCode']);
        $this->assertSame('pcs', $formatted['lines'][0]['unit']);
        $this->assertSame('69.60', $formatted['totals']['gross']);
        $this->assertSame('30 November 2026', $formatted['paymentTerms'][0]['due']);
    }

    public function testReadsThroughAMapGivenUnderItsOwnNames(): void
    {
        $map = '<cross-industry-invoice-map prefix="rechnung"><value name="nummer" path="rsm:ExchangedDocument/ram:ID"/></cross-industry-invoice-map>';
        $data = LCrossIndustryInvoice::extractInvoiceData(self::invoice('en16931.xml'), new ExtractInvoiceDataOptions(invoiceMap: $map));
        $this->assertSame(['nummer' => '2026-0412'], $data);
    }

    public function testGivesTheDefaultMapAsXmlText(): void
    {
        $map = LCrossIndustryInvoice::defaultInvoiceMap();
        $this->assertStringStartsWith('<?xml', $map);
        $this->assertStringContainsString('<cross-industry-invoice-map prefix="invoice">', $map);
    }

    public function testRefusesWhatTheEngineRefusesWithItsError(): void
    {
        try {
            LCrossIndustryInvoice::extractInvoiceData(self::invoice('minimum.xml'));
            $this->fail('a MINIMUM invoice is refused');
        } catch (EngineException $e) {
            $this->assertStringContainsString("the invoice's profile is MINIMUM", $e->getMessage());
        }
        $typo = str_replace('invoice:seller.name', 'invoice:sellr.name', self::template());
        $this->expectException(EngineException::class);
        $this->expectExceptionMessage("data-value=\"invoice:sellr.name\": 'sellr' is not in the map");
        LCrossIndustryInvoice::generatePdf(self::engine(), $typo, new GeneratePdfOptions(
            invoiceXml: self::invoice('en16931.xml'), createdOn: self::CREATED_ON,
        ));
    }
}
