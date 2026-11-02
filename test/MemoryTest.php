<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\EngineException;
use Lpdf\Engine\EngineOptions;
use Lpdf\Engine\RenderOptions;
use Lpdf\Kit\PdfDocument;
use Lpdf\L;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Big documents, the memory cap of a render, how deep a document may nest, and an engine that crashes. */
final class MemoryTest extends TestCase
{
    private const CAP_ERROR = 'lpdf render error: the render needs more memory than its cap of ';
    private const DEPTH_ERROR = 'is nested 101 levels deep, past the limit of 100';

    /** @var list<string> */
    private array $standIns = [];

    protected function tearDown(): void
    {
        foreach ($this->standIns as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testBenchMaxCopied16TimesRendersIts3008Pages(): void
    {
        $this->needsFixtures();
        $xml = self::benchMaxCopied(16);
        $this->assertGreaterThan(16_000_000, strlen($xml));

        $pdf = L::engine(new EngineOptions(timeout: 0))->render($xml);

        $this->assertSame(3008, self::pageCount($pdf));
    }

    public function testADocumentTreeWhoseJsonIsOver4MbRenders(): void
    {
        $line = str_repeat('A paragraph of a long report, with enough words in it to fill a line or two of the page. ', 2);
        $sections = [];
        for ($s = 0; $s < 1200; $s++) {
            $texts = [];
            for ($i = 0; $i < 20; $i++) {
                $texts[] = L::text(null, ["$s.$i $line"]);
            }
            $sections[] = L::section(null, [L::layout(null, $texts)]);
        }
        $tree = L::document(null, $sections);
        $this->assertGreaterThan(4 * 1_048_576, strlen(json_encode($tree, JSON_THROW_ON_ERROR)));

        $pdf = L::engine(new EngineOptions(timeout: 0))->render($tree);

        $this->assertGreaterThanOrEqual(1200, self::pageCount($pdf));
    }

    public function testARenderPastTheCapStopsWithItsErrorAndTheNextRendersRight(): void
    {
        $this->needsFixtures();
        $engine = L::engine(new EngineOptions(maxMemoryMb: 8))->setLicenseKey('test-key');

        try {
            $engine->render(self::fixture('bench_max'));
            $this->fail('the render passed its cap');
        } catch (EngineException $e) {
            $this->assertStringStartsWith(self::CAP_ERROR . '8 MB', $e->getMessage());
        }
        SnapshotHelper::compareOrUpdate('bench_m', $engine->render(self::fixture('bench_m'), new RenderOptions(skipEncryption: true)));
    }

    public function testTheSameDocumentRendersUnderAHigherCap(): void
    {
        $this->needsFixtures();

        $pdf = L::engine(new EngineOptions(maxMemoryMb: 64))->render(self::fixture('bench_max'));

        $this->assertSame(188, self::pageCount($pdf));
    }

    public function testABigDataObjectCountsTowardTheCap(): void
    {
        $xml = '<lpdf version="1"><document><section><layout><text data-value="title">x</text></layout></section></document></lpdf>';
        // 8 MB of text in rows the template doesn't show: PHP's own memory_limit, 128 MB by default, holds it.
        $rows = [];
        for ($i = 0; $i < 40_000; $i++) {
            $rows[] = str_repeat('Some text that is bound. ', 8) . $i;
        }
        $data = new RenderOptions(data: ['title' => 'Report', 'rows' => $rows]);

        try {
            L::engine(new EngineOptions(maxMemoryMb: 4))->render($xml, $data);
            $this->fail('the render passed its cap');
        } catch (EngineException $e) {
            $this->assertStringStartsWith(self::CAP_ERROR . '4 MB', $e->getMessage());
        }
        $this->assertSame(1, self::pageCount(L::engine(new EngineOptions(maxMemoryMb: 64))->render($xml, $data)));
    }

    /** @return array<string, array{int}> */
    public static function capsOutOfRange(): array
    {
        return ['0' => [0], '-1' => [-1], '4097' => [4097]];
    }

    #[DataProvider('capsOutOfRange')]
    public function testACapBelow1OrAbove4096IsRefused(int $megabytes): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("maxMemoryMb must be a whole number of megabytes from 1 to 4096, not $megabytes");

        new EngineOptions(maxMemoryMb: $megabytes);
    }

    public function testADocumentNested100LevelsDeepRendersAsXmlAndAsATree(): void
    {
        $engine = L::engine();

        $this->assertSame(1, self::pageCount($engine->render(self::nestedXml(100))));
        $this->assertSame(1, self::pageCount($engine->render(self::nestedTree(100))));
    }

    public function testADocumentNested101LevelsDeepFailsWithTheDepthErrorAsXmlAndAsATree(): void
    {
        $engine = L::engine();

        foreach ([[self::nestedXml(101), '<text> at line 1'], [self::nestedTree(101), "the node 'text'"]] as [$input, $named]) {
            try {
                $engine->render($input);
                $this->fail('the render went past the depth limit');
            } catch (EngineException $e) {
                $this->assertStringContainsString(self::DEPTH_ERROR, $e->getMessage());
                $this->assertStringStartsWith("lpdf render error: $named", $e->getMessage());
            }
        }
    }

    /** @return array<string, array{string, string}> */
    public static function traps(): array
    {
        return [
            'unreachable' => ['(module (func (export "_start") unreachable))', 'wasm `unreachable` instruction executed'],
            'stack overflow' => ['(module (func $down (call $down)) (func (export "_start") (call $down)))', 'call stack exhausted'],
        ];
    }

    #[DataProvider('traps')]
    public function testACrashThrowsTheCrashMessageWithWasmtimesErrorInside(string $wat, string $trap): void
    {
        $path = tempnam(sys_get_temp_dir(), 'lpdf-stand-in-');
        $this->assertIsString($path);
        file_put_contents($path, $wat);
        $this->standIns[] = $path;

        try {
            L::engine(new EngineOptions(wasmBinary: $path))->render(self::nestedXml(10));
            $this->fail('the stand-in did not crash');
        } catch (EngineException $e) {
            $this->assertSame(
                'The engine crashed during the render. This is a bug in Lpdf, or the render ran out of memory before its '
                . 'cap stopped it: please report it with the document at https://github.com/lpdfio/lpdf/issues.',
                $e->getMessage(),
            );
            $this->assertStringContainsString($trap, (string) $e->getPrevious()?->getMessage());
        }
    }

    private function needsFixtures(): void
    {
        if (!SnapshotHelper::hasFixtures()) {
            $this->markTestSkipped('Fixture files not available outside the monorepo.');
        }
    }

    private static function fixture(string $name): string
    {
        return (string) file_get_contents(SnapshotHelper::fixtures() . "/$name.xml");
    }

    /** `bench_max.xml` with its sections copied: 188 pages and 1 MB of XML for each copy. */
    private static function benchMaxCopied(int $copies): string
    {
        $xml = self::fixture('bench_max');
        $start = (int) strpos($xml, '<section');
        $end = (int) strrpos($xml, '</section>') + strlen('</section>');
        return substr($xml, 0, $start) . str_repeat(substr($xml, $start, $end - $start), $copies) . substr($xml, $end);
    }

    /** A document whose columns nest so that its deepest element is $depth levels deep, `<lpdf>` included. */
    private static function nestedXml(int $depth): string
    {
        // <lpdf>, <document>, <section>, <layout> and the <text> in the deepest column.
        $columns = $depth - 5;
        return '<lpdf version="1"><document><section><layout>' . str_repeat('<column>', $columns) . '<text>deep</text>'
            . str_repeat('</column>', $columns) . '</layout></section></document></lpdf>';
    }

    /** The tree of nestedXml($depth). */
    private static function nestedTree(int $depth): PdfDocument
    {
        $node = L::text(null, ['deep']);
        for ($i = 0; $i < $depth - 5; $i++) {
            $node = L::column(null, [$node]);
        }
        return L::document(null, [L::section(null, [L::layout(null, [$node])])]);
    }

    /** The number of pages of a PDF: its page objects, which the engine writes uncompressed. */
    private static function pageCount(string $pdf): int
    {
        return (int) preg_match_all('#/Type /Page\b(?!s)#', $pdf);
    }
}
