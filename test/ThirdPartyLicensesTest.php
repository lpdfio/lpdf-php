<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use PHPUnit\Framework\TestCase;

/**
 * The licenses of the crates the engine links ask for their text to travel with it: the package carries the file that
 * core's release attaches beside the engine, in resources/ with lpdf-wasi.wasm.
 */
final class ThirdPartyLicensesTest extends TestCase
{
    private const FILE = __DIR__ . '/../resources/THIRD_PARTY_LICENSES_ENGINE.txt';

    public function testTheFileIsBesideTheEngine(): void
    {
        $this->assertFileExists(__DIR__ . '/../resources/lpdf-wasi.wasm');
        $this->assertFileExists(self::FILE, 'The core release carries it beside lpdf-wasi.wasm.');
    }

    public function testTheFileNamesTheCratesTheEngineLinksWithTheirLicenseTexts(): void
    {
        $text = (string) file_get_contents(self::FILE);

        $this->assertStringStartsWith("THIRD-PARTY LICENSES OF THE LPDF ENGINE\n", $text);
        $this->assertMatchesRegularExpression('/\nCRATES \(\d+\)\n/', $text);
        $this->assertMatchesRegularExpression('/\nLICENSE TEXTS \(\d+\)\n/', $text);
        $this->assertMatchesRegularExpression('/\nserde \d+\.\d+\.\d+ \| /', $text);
    }
}
