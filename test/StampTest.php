<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\EngineException;
use Lpdf\Engine\StampAttr;
use Lpdf\L;
use Lpdf\PdfEngine;
use PHPUnit\Framework\TestCase;

/** Stamps: PNG images drawn over the pages of an existing PDF, through the engine's `stamp` request. */
final class StampTest extends TestCase
{
    /** A PNG chunk; the decoder doesn't check the CRC, which is left 0. */
    private static function chunk(string $tag, string $data): string
    {
        return pack('N', strlen($data)) . $tag . $data . "\0\0\0\0";
    }

    /** An RGB PNG of `$width` by `$height` pixels, all one color. */
    private static function png(int $width, int $height, string $rgb = "\xC8\x28\x28"): string
    {
        $header = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
        $rows = str_repeat("\0" . str_repeat($rgb, $width), $height);
        return "\x89PNG\r\n\x1A\n" . self::chunk('IHDR', $header) . self::chunk('IDAT', gzcompress($rows))
            . self::chunk('IEND', '');
    }

    /** A PDF of `$pages` pages, rendered by the engine. */
    private static function pdf(int $pages = 2): string
    {
        $sections = '';
        for ($i = 1; $i <= $pages; $i++) {
            $sections .= "<section><layout><text>Page {$i}</text></layout></section>";
        }
        return (new PdfEngine())->render("<lpdf version=\"1\"><document>{$sections}</document></lpdf>");
    }

    public function testAStampReturnsTheInputWithAnUpdateAfterItThatDrawsTheImage(): void
    {
        $pdf = self::pdf();
        $out = L::engine()->stamp($pdf, [
            new StampAttr(image: self::png(60, 30), page: 'last', x: '72pt', y: '72pt', width: '144pt', height: '36pt', justify: 'end'),
        ]);
        $this->assertGreaterThan(strlen($pdf), strlen($out));
        $this->assertSame($pdf, substr($out, 0, strlen($pdf)));
        $this->assertStringContainsString('/LpdfStamp0 Do', substr($out, strlen($pdf)));
    }

    public function testTheSameBytesGivenToSeveralStampsAreEmbeddedOnce(): void
    {
        $pdf = self::pdf(3);
        $seal = self::png(40, 40);
        $out = (new PdfEngine())->stamp($pdf, [
            new StampAttr(image: $seal, page: 'each', anchor: 'top-right', x: '-36pt', y: '36pt', width: '40pt', height: '40pt'),
            new StampAttr(image: $seal, page: '1', anchor: 'center', width: '40pt', height: '40pt', maxGrow: 2),
            new StampAttr(image: self::png(20, 10, "\x00\x00\xC8"), page: '2-3', x: '1in', y: '1in', width: '1in', height: '1in'),
        ]);
        $this->assertSame(2, preg_match_all('#/Subtype\s*/Image#', substr($out, strlen($pdf))));
    }

    public function testWithoutALicenseKeyEachStampGetsTheAttributionLineLinkedToLpdfIo(): void
    {
        $pdf = self::pdf(1);
        $out = (new PdfEngine())->stamp($pdf, [
            new StampAttr(image: self::png(10, 10), page: '1', anchor: 'center', width: '50pt', height: '50pt'),
        ]);
        $this->assertStringContainsString('(https://lpdf.io)', substr($out, strlen($pdf)));
    }

    public function testAPageThePdfDoesNotHaveIsAnEngineError(): void
    {
        $this->expectException(EngineException::class);
        $this->expectExceptionMessage('stamp 1: the PDF has no page 3: it has 2 pages');
        (new PdfEngine())->stamp(self::pdf(2), [
            new StampAttr(image: self::png(10, 10), page: '3', x: '0pt', y: '0pt', width: '10pt', height: '10pt'),
        ]);
    }

    public function testAnImageThatIsNotAPngIsAnEngineError(): void
    {
        $this->expectException(EngineException::class);
        $this->expectExceptionMessage('the image is a JPEG, and a stamp takes a PNG');
        (new PdfEngine())->stamp(self::pdf(1), [
            new StampAttr(image: "\xFF\xD8\xFF\xE0\0\0", page: '1', x: '0pt', y: '0pt', width: '10pt', height: '10pt'),
        ]);
    }
}
