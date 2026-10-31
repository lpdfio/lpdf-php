<?php

declare(strict_types=1);

namespace Lpdf\Tests;

use Lpdf\Engine\EngineOptions;
use Lpdf\L;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The `src` of a font, an image, a file and a file of Markdown rules is found in an XML document as the engine reads
 * the document: entities decoded, a `>` in a quoted value, either quote, and what is in a comment or a CDATA section
 * is text. Each render goes to a stand-in for the engine, which keeps the request it is sent.
 */
final class XmlSrcTest extends TestCase
{
    private const RED_TITLES = '<markdown-styles><h1 color="#ff0000"/></markdown-styles>';

    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/lpdf-srcs-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
        file_put_contents(
            "{$this->dir}/engine",
            "#!/bin/sh\ncat > \"\$(dirname \"\$0\")/request.json\"\nprintf '{\"pdf\":\"JVBERi0=\"}'\n",
        );
        chmod("{$this->dir}/engine", 0755);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->dir}/*") ?: []);
        rmdir($this->dir);
    }

    /** @return array<string, array{string, string, string, string}> The kind, the declaration, its file and its key. */
    public static function declared(): array
    {
        return [
            'a font with an entity'     => ['fonts', '<font name="brand" ref="R&amp;D Sans" src="{dir}/R&amp;D Sans.ttf"/>', 'R&D Sans.ttf', 'R&D Sans'],
            'a font after a >'          => ['fonts', '<font name="brand" ref="rows > 1000" src="{dir}/brand.ttf"/>', 'brand.ttf', 'rows > 1000'],
            'a font in single quotes'   => ['fonts', "<font name='brand' src='{dir}/brand.ttf'/>", 'brand.ttf', 'brand'],
            'an image with an entity'   => ['images', '<image name="logo" ref="R&amp;D logo" src="{dir}/R&amp;D logo.png"/>', 'R&D logo.png', 'R&D logo'],
            'an image after a >'        => ['images', '<image name="logo" ref="rows > 1000" src="{dir}/logo.png"/>', 'logo.png', 'rows > 1000'],
            'an image in single quotes' => ['images', "<image name='logo' src='{dir}/logo.png'/>", 'logo.png', 'logo'],
            'a file with an entity'     => ['files', '<file name="R&amp;D.csv" src="{dir}/R&amp;D.csv"/>', 'R&D.csv', 'R&D.csv'],
            'a file after a >'          => ['files', '<file name="rows.csv" description="rows > 1000" src="{dir}/rows.csv"/>', 'rows.csv', 'rows.csv'],
            'a file in single quotes'   => ['files', "<file name='rows.csv' src='{dir}/rows.csv'/>", 'rows.csv', 'rows.csv'],
        ];
    }

    #[DataProvider('declared')]
    public function testASrcIsReadAndLoadedUnderItsRefOrName(string $kind, string $declaration, string $file, string $key): void
    {
        file_put_contents("{$this->dir}/$file", "the bytes of $file");
        $declaration = str_replace('{dir}', $this->dir, $declaration);

        $request = $this->request($kind === 'files' ? self::xml(attachments: $declaration) : self::xml(assets: $declaration));

        self::assertSame([$key => base64_encode("the bytes of $file")], $request[$kind] ?? []);
    }

    /** @return array<string, array{string, string}> The styles and their file. */
    public static function markdownStyles(): array
    {
        return [
            'with an entity'   => ['<markdown-styles src="{dir}/R&amp;D styles.xml"/>', 'R&D styles.xml'],
            'after a >'        => ['<markdown-styles xmlns:rows="urn:rows>1000" src="{dir}/styles.xml"/>', 'styles.xml'],
            'in single quotes' => ["<markdown-styles src='{dir}/styles.xml'/>", 'styles.xml'],
        ];
    }

    #[DataProvider('markdownStyles')]
    public function testAFileOfRulesIsReadFromTheSrcOfItsStyles(string $styles, string $file): void
    {
        file_put_contents("{$this->dir}/$file", self::RED_TITLES);
        $styles = str_replace('{dir}', $this->dir, $styles);

        $request = $this->request(self::xml(section: "<markdown>$styles<markdown-content># Title</markdown-content></markdown>"));

        self::assertSame(["{$this->dir}/$file" => self::RED_TITLES], $request['markdown_styles'] ?? []);
    }

    /** @return array<string, array{string}> */
    public static function hidden(): array
    {
        return [
            'a font'          => ['<assets><font name="brand" src="{dir}/hidden"/></assets>'],
            'an image'        => ['<assets><image name="logo" src="{dir}/hidden"/></assets>'],
            'a file'          => ['<attachments><file name="rows.csv" src="{dir}/hidden"/></attachments>'],
            'a file of rules' => ['<markdown><markdown-styles src="{dir}/hidden"/></markdown>'],
        ];
    }

    #[DataProvider('hidden')]
    public function testASrcInACommentOrACdataSectionIsTextAndIsNotRead(string $declaration): void
    {
        file_put_contents("{$this->dir}/hidden", 'hidden');
        $declaration = str_replace('{dir}', $this->dir, $declaration);

        $request = $this->request(self::xml(section: "<layout><!-- $declaration --><code><![CDATA[$declaration]]></code></layout>"));

        self::assertSame([], array_intersect_key($request, array_flip(['fonts', 'images', 'files', 'markdown_styles'])));
    }

    private static function xml(string $assets = '', string $attachments = '', string $section = '<layout><text>x</text></layout>'): string
    {
        return "<lpdf version=\"1\"><assets>$assets</assets><document><attachments>$attachments</attachments>"
            . "<section>$section</section></document></lpdf>";
    }

    /** @return array<string, mixed> The request a render of `$xml` sends the engine. */
    private function request(string $xml): array
    {
        L::engine(new EngineOptions(wasmRunner: "{$this->dir}/engine"))->setLicenseKey('test-key')->render($xml);
        $request = json_decode((string) file_get_contents("{$this->dir}/request.json"), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($request);
        return $request;
    }
}
