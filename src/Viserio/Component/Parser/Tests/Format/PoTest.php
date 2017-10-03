<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Format;

use PHPUnit\Framework\TestCase;
use Throwable;
use Viserio\Component\Parser\Parser\PoParser;

class PoTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parser\Parser\PoParser
     */
    private $parser;

    /**
     * @var string
     */
    private $fixturePath;

    public function setUp()
    {
        parent::setUp();

        $this->parser      = new PoParser();
        $this->fixturePath = __DIR__ . '/../Fixtures/po';
    }

    public function testRead()
    {
        try {
            $result = $this->parser->parse(self::readFile($this->fixturePath . '/healthy.po'));
        } catch (Throwable $e) {
            $result = [];

            $this->fail($e->getMessage());
        }

        self::assertCount(3, $result);
        self::assertTrue(array_key_exists('headers', $result));

        // Read file without headers.
        // It should not skip first entry
        try {
            $result = $this->parser->parse(self::readFile($this->fixturePath . '/noheader.po'));
        } catch (Throwable $e) {
            $result = [];
            $this->fail($e->getMessage());
        }

        self::assertCount(2, $result, 'Did not read properly po file without headers.');
        self::assertFalse(array_key_exists('headers', $result));
    }

    public function testHeaders()
    {
        try {
            $result  = $this->parser->parse(self::readFile($this->fixturePath . '/healthy.po'));
            $headers = $result['headers'];

            self::assertCount(18, $headers);
            self::assertSame('', $headers['Project-Id-Version']);
            self::assertSame('', $headers['Report-Msgid-Bugs-To']);
            self::assertSame('2017-09-28 15:55+0100', $headers['POT-Creation-Date']);
            self::assertSame('', $headers['PO-Revision-Date']);
            self::assertSame('Narrowspark <EMAIL@ADDRESS>', $headers['Last-Translator']);
            self::assertSame('', $headers['Language-Team']);
            self::assertSame('1.0', $headers['MIME-Version']);
            self::assertSame('text/plain; charset=UTF-8', $headers['Content-Type']);
            self::assertSame('8bit', $headers['Content-Transfer-Encoding']);
            self::assertSame('nplurals=2; plural=n != 1;', $headers['Plural-Forms']);
            self::assertSame('UTF-8', $headers['X-Poedit-SourceCharset']);
            self::assertSame('__;_e;_n;_t', $headers['X-Poedit-KeywordsList']);
            self::assertSame('yes', $headers['X-Textdomain-Support']);
            self::assertSame('.', $headers['X-Poedit-Basepath']);
            self::assertSame('Poedit 2.0.4', $headers['X-Generator']);
            self::assertSame('.', $headers['X-Poedit-SearchPath-0']);
            self::assertSame('../..', $headers['X-Poedit-SearchPath-1']);
            self::assertSame('../../../modules', $headers['X-Poedit-SearchPath-2']);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testMultilineId()
    {
        try {
            $result = $this->parser->parse(self::readFile($this->fixturePath . '/multilines.po'));
            var_dump($result);
            self::assertCount(18, $result['headers']);
            self::assertCount(9, $result['msgid']);
            self::assertCount(9, $result['msgstr']);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    private static function readFile(string $filePath): string
    {
        return file_get_contents($filePath);
    }
}
