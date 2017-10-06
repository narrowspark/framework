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
        self::assertSame(
            [
                'msgid'      => ['Lo sentimos, ha ocurrido un error...'],
                'msgstr'     => ['Ho sentim, s\'ha produït un error'],
                'msgctxt'    => [],
                'ccomment'   => [],
                'tcomment'   => [],
                'obsolete'   => false,
                'fuzzy'      => false,
                'flags'      => [],
                'references' => ['{../../classes/dddddd.php}:{33}' => [
                    '../../classes/dddddd.php',
                    '33',
                ]],
            ],
            reset($result)
        );

        // Read file without headers.
        // It should not skip first entry
        try {
            $result = $this->parser->parse(self::readFile($this->fixturePath . '/noheader.po'));
        } catch (Throwable $e) {
            $result = [];
            $this->fail($e->getMessage());
        }

        self::assertCount(3, $result, 'Did not read properly po file without headers.');
        self::assertCount(0, $result['headers']);
    }

    public function testHeaders()
    {
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
    }

    public function testMultiLinesHeaders()
    {
        $result  = $this->parser->parse(self::readFile($this->fixturePath . '/multiline-header.po'));
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
        // a multi-line header value
        self::assertSame('nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);', $headers['Plural-Forms']);
        self::assertSame('UTF-8', $headers['X-Poedit-SourceCharset']);
        self::assertSame('__;_e;_n;_t', $headers['X-Poedit-KeywordsList']);
        self::assertSame('yes', $headers['X-Textdomain-Support']);
        self::assertSame('.', $headers['X-Poedit-Basepath']);
        self::assertSame('Poedit 2.0.4', $headers['X-Generator']);
        self::assertSame('.', $headers['X-Poedit-SearchPath-0']);
        self::assertSame('../..', $headers['X-Poedit-SearchPath-1']);
        self::assertSame('../../../modules', $headers['X-Poedit-SearchPath-2']);
    }

    public function testMultiLineId()
    {
        $result = $this->parser->parse(self::readFile($this->fixturePath . '/multilines.po'));

        self::assertSame(
            [
                'msgid'      => ['Lo sentimos, ha ocurrido un error...'],
                'msgstr'     => ['Ho sentim, s\'ha produït un error'],
                'msgctxt'    => [],
                'ccomment'   => [],
                'tcomment'   => ['@ default'],
                'obsolete'   => false,
                'fuzzy'      => false,
                'flags'      => [],
                'references' => ['{../../classes/dddddd.php}:{33}' => [
                    '../../classes/dddddd.php',
                    '33',
                ]],
            ],
            reset($result)
        );

        unset($result['headers']);

        self::assertSame(
            [
                'msgid'      => [
                    '',
                    'El archivo {file} es demasiado pequeño, el tamaño mínimo de archivo es ',
                    '{minSizeLimit}.',
                ],
                'msgstr'     => [
                    '',
                    'El fitxer {file} es massa petit, el tamany mínim de fitxer es ',
                    '{minSizeLimit}.',
                ],
                'msgctxt'    => [],
                'ccomment'   => [],
                'tcomment'   => ['@ default'],
                'obsolete'   => true,
                'fuzzy'      => false,
                'flags'      => [],
                'references' => [],
            ],
            end($result)
        );
    }

    private static function readFile(string $filePath): string
    {
        return file_get_contents($filePath);
    }
}
