<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\MergeOptions;
use Lpdf\Engine\MergeSource;
use Lpdf\Engine\RenderOptions;
use Lpdf\PdfEngine;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Merge: existing PDFs drawn into one PDF made for printing, through the engine's `merge` request, with each runner.
 */
final class MergeTest extends TestCase
{
    /** What the runners put before the engine's own message. */
    private const ERROR_PREFIX = 'lpdf render error: ';

    /** @return array<string, array{string}> Each runner, by name. */
    public static function runners(): array
    {
        return RunnerSupport::runners();
    }

    /** A PDF of `$pages` pages, rendered by the engine, with `$output` for its output and the options of the render. */
    private static function pdf(int $pages = 1, string $output = '', ?RenderOptions $options = null): string
    {
        $sections = '';
        for ($i = 1; $i <= $pages; $i++) {
            $sections .= "<section><layout><text>Page {$i}</text></layout></section>";
        }
        return (new PdfEngine())->render("<lpdf version=\"1\"><document>{$output}{$sections}</document></lpdf>", $options);
    }

    /** A PDF of one empty page whose catalog holds a certifying signature, which is what makes a file signed. */
    private static function signedPdf(): string
    {
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R /Perms << /DocMDP 4 0 R >> >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] >>',
            '<< /Type /Sig /Filter /Adobe.PPKLite /SubFilter /adbe.pkcs7.detached >>',
        ];
        $pdf = "%PDF-1.7\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        return $pdf . 'trailer << /Size ' . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }

    /** How many pages a merged PDF has: its page objects, which the merge writes uncompressed. */
    private static function pageCount(string $pdf): int
    {
        return preg_match_all('#/Type\s*/Page(?![s\w])#', $pdf);
    }

    private static function engine(string $runner): PdfEngine
    {
        RunnerSupport::need($runner);
        return new PdfEngine(new EngineOptions(runner: $runner));
    }

    /** The message of the error `$merge` throws, without what the runner puts before the engine's own words. */
    private function engineErrorOf(callable $merge): string
    {
        try {
            $merge();
        } catch (EngineException $e) {
            $this->assertStringStartsWith(self::ERROR_PREFIX, $e->getMessage());
            return substr($e->getMessage(), strlen(self::ERROR_PREFIX));
        }
        $this->fail('the merge did not throw an EngineException');
    }

    #[DataProvider('runners')]
    public function testMergeReturnsOnePdfWithThePagesOfTheSourcesInOrder(string $runner): void
    {
        $output = self::engine($runner)->merge([
            new MergeSource(name: 'a.pdf', pdf: self::pdf(2)),
            new MergeSource(name: 'b.pdf', pdf: self::pdf(1)),
        ]);

        $this->assertStringStartsWith('%PDF-', $output);
        $this->assertSame(3, self::pageCount($output));
    }

    #[DataProvider('runners')]
    public function testDoubleSidedAddsABlankSideAfterADocumentWithAnOddNumberOfPagesAndNoneAfterTheLast(string $runner): void
    {
        $odd = self::pdf(1);
        $sources = [
            new MergeSource(name: 'odd.pdf', pdf: $odd),
            new MergeSource(name: 'even.pdf', pdf: self::pdf(2)),
            new MergeSource(name: 'odd-again.pdf', pdf: $odd),
        ];
        $engine = self::engine($runner);

        $this->assertSame(4, self::pageCount($engine->merge($sources)));
        $this->assertSame(5, self::pageCount($engine->merge($sources, new MergeOptions(doubleSided: true))));
    }

    #[DataProvider('runners')]
    public function testAFileThatIsNotAPdfIsAnEngineErrorThatNamesIt(string $runner): void
    {
        $engine = self::engine($runner);
        $sources = [new MergeSource(name: 'a.pdf', pdf: self::pdf()), new MergeSource(name: 'notes.txt', pdf: 'hello')];

        $this->assertSame(
            "notes.txt: the file is not a PDF: it doesn't start with %PDF-",
            $this->engineErrorOf(static fn () => $engine->merge($sources)),
        );
    }

    #[DataProvider('runners')]
    public function testAnEmptyListIsAnEngineError(string $runner): void
    {
        $engine = self::engine($runner);

        $this->assertSame(
            'no PDFs were given: give merge at least one',
            $this->engineErrorOf(static fn () => $engine->merge([])),
        );
    }

    #[DataProvider('runners')]
    public function testAFileWhoseOwnerRestrictsPrintingIsRefusedUnlessAllowRestrictedIsSet(string $runner): void
    {
        $limited = self::pdf(1, '<output encrypt="aes-256" allow="print"/>', new RenderOptions(ownerPassword: 'owner'));
        $sources = [new MergeSource(name: 'limited.pdf', pdf: $limited)];
        $engine = self::engine($runner);

        $this->assertSame(
            'limited.pdf: the owner restricts printing or copying: set the allow-restricted option to merge it anyway',
            $this->engineErrorOf(static fn () => $engine->merge($sources)),
        );

        $output = $engine->merge($sources, new MergeOptions(allowRestricted: true));
        $this->assertSame(1, self::pageCount($output));
        $this->assertStringNotContainsString('/Encrypt', $output);
    }

    #[DataProvider('runners')]
    public function testASignedFileIsRefusedUnlessAllowSignedIsSet(string $runner): void
    {
        $sources = [new MergeSource(name: 'signed.pdf', pdf: self::signedPdf())];
        $engine = self::engine($runner);

        $error = $this->engineErrorOf(static fn () => $engine->merge($sources));
        $this->assertStringStartsWith('signed.pdf: the file is signed', $error);
        $this->assertStringContainsString('set the allow-signed option', $error);

        $this->assertSame(1, self::pageCount($engine->merge($sources, new MergeOptions(allowSigned: true))));
    }

    public function testAnItemThatIsNotAMergeSourceThrowsBeforeTheEngineIsAsked(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('source 2 is not a MergeSource');

        (new PdfEngine())->merge([new MergeSource(name: 'a.pdf', pdf: self::pdf()), ['name' => 'b.pdf']]);
    }
}
