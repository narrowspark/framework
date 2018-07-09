<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Format;

use PHPUnit\Framework\TestCase;
use Throwable;
use Viserio\Component\Contract\Parser\Exception\ParseException;
use Viserio\Component\Parser\Dumper\PoDumper;
use Viserio\Component\Parser\Parser\PoParser;

/**
 * @internal
 */
final class PoTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parser\Parser\PoParser
     */
    private $parser;

    /**
     * @var \Viserio\Component\Parser\Dumper\PoDumper
     */
    private $dumper;

    /**
     * @var string
     */
    private $fixturePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->parser      = new PoParser();
        $this->dumper      = new PoDumper();
        $this->fixturePath = __DIR__ . '/../Fixture/po';
    }

    public function testRead(): void
    {
        try {
            $result = $this->parser->parse(\file_get_contents($this->fixturePath . '/healthy.po'));
        } catch (Throwable $e) {
            $result = [];

            static::fail($e->getMessage());
        }

        static::assertCount(3, $result);
        static::assertArrayHasKey('headers', $result);
        static::assertSame(
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
            $result[0]
        );
        static::assertSame(
            [
                'msgid'      => ['Debes indicar un nombre.'],
                'msgstr'     => ['Has d\'indicar un nom.'],
                'msgctxt'    => [],
                'ccomment'   => [],
                'tcomment'   => [],
                'obsolete'   => false,
                'fuzzy'      => false,
                'flags'      => [],
                'references' => [
                    '{../../classes/xxxxx.php}:{96} {../../classes/controller/iiiiiii.php}:{107} {skycomponents/equator.php}:{31}' => [
                        [
                            '../../classes/xxxxx.php',
                            '96',
                        ],
                        [
                            '../../classes/controller/iiiiiii.php',
                            '107',
                        ],
                        [
                            'skycomponents/equator.php',
                            '31',
                        ],
                    ],
                    '{../../classes/controller/yyyyyyy/zzzzzz.php}:{288}' => [
                        '../../classes/controller/yyyyyyy/zzzzzz.php',
                        '288',
                    ],
                ],
            ],
            $result[1]
        );

        // Read file without headers.
        // It should not skip first entry
        try {
            $result = $this->parser->parse(\file_get_contents($this->fixturePath . '/noheader.po'));
        } catch (Throwable $e) {
            $result = [];
            static::fail($e->getMessage());
        }

        static::assertCount(3, $result, 'Did not read properly po file without headers.');
        static::assertCount(0, $result['headers']);
    }

    public function testHeaders(): void
    {
        $result  = $this->parser->parse(\file_get_contents($this->fixturePath . '/healthy.po'));
        $headers = $result['headers'];

        static::assertCount(18, $headers);
        static::assertSame('', $headers['Project-Id-Version']);
        static::assertSame('', $headers['Report-Msgid-Bugs-To']);
        static::assertSame('2017-09-28 15:55+0100', $headers['POT-Creation-Date']);
        static::assertSame('', $headers['PO-Revision-Date']);
        static::assertSame('Narrowspark <EMAIL@ADDRESS>', $headers['Last-Translator']);
        static::assertSame('', $headers['Language-Team']);
        static::assertSame('1.0', $headers['MIME-Version']);
        static::assertSame('text/plain; charset=UTF-8', $headers['Content-Type']);
        static::assertSame('8bit', $headers['Content-Transfer-Encoding']);
        static::assertSame('nplurals=2; plural=n != 1;', $headers['Plural-Forms']);
        static::assertSame('UTF-8', $headers['X-Poedit-SourceCharset']);
        static::assertSame('__;_e;_n;_t', $headers['X-Poedit-KeywordsList']);
        static::assertSame('yes', $headers['X-Textdomain-Support']);
        static::assertSame('.', $headers['X-Poedit-Basepath']);
        static::assertSame('Poedit 2.0.4', $headers['X-Generator']);
        static::assertSame('.', $headers['X-Poedit-SearchPath-0']);
        static::assertSame('../..', $headers['X-Poedit-SearchPath-1']);
        static::assertSame('../../../modules', $headers['X-Poedit-SearchPath-2']);
    }

    public function testMultiLinesHeaders(): void
    {
        $result  = $this->parser->parse(\file_get_contents($this->fixturePath . '/multiline-header.po'));
        $headers = $result['headers'];

        static::assertCount(18, $headers);
        static::assertSame('', $headers['Project-Id-Version']);
        static::assertSame('', $headers['Report-Msgid-Bugs-To']);
        static::assertSame('2017-09-28 15:55+0100', $headers['POT-Creation-Date']);
        static::assertSame('', $headers['PO-Revision-Date']);
        static::assertSame('Narrowspark <EMAIL@ADDRESS>', $headers['Last-Translator']);
        static::assertSame('', $headers['Language-Team']);
        static::assertSame('1.0', $headers['MIME-Version']);
        static::assertSame('text/plain; charset=UTF-8', $headers['Content-Type']);
        static::assertSame('8bit', $headers['Content-Transfer-Encoding']);
        // a multi-line header value
        static::assertSame(
            [
                'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n',
                '%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);',
            ],
            $headers['Plural-Forms']
        );
        static::assertSame('UTF-8', $headers['X-Poedit-SourceCharset']);
        static::assertSame('__;_e;_n;_t', $headers['X-Poedit-KeywordsList']);
        static::assertSame('yes', $headers['X-Textdomain-Support']);
        static::assertSame('.', $headers['X-Poedit-Basepath']);
        static::assertSame('Poedit 2.0.4', $headers['X-Generator']);
        static::assertSame('.', $headers['X-Poedit-SearchPath-0']);
        static::assertSame('../..', $headers['X-Poedit-SearchPath-1']);
        static::assertSame('../../../modules', $headers['X-Poedit-SearchPath-2']);
    }

    public function testMultiLineId(): void
    {
        $result = $this->parser->parse(\file_get_contents($this->fixturePath . '/multilines.po'));

        static::assertSame(
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
            \reset($result)
        );

        unset($result['headers']);

        static::assertSame(
            [
                'msgid' => [
                    '',
                    'El archivo {file} es demasiado pequeño, el tamaño mínimo de archivo es ',
                    '{minSizeLimit}.',
                ],
                'msgstr' => [
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
            \end($result)
        );
        static::assertSame(
            [
                'msgid' => [
                    'El archivo supera el tamaño máximo permitido: %size%MB',
                ],
                'msgstr' => [
                    '',
                    'El fitxer {file} es massa gran, el tamany máxim de fitxer es {sizeLimit}.',
                ],
                'msgctxt'  => [],
                'ccomment' => [],
                'tcomment' => ['@ default'],
                'obsolete' => false,
                'fuzzy'    => true,
                'flags'    => [
                    'fuzzy',
                ],
                'references' => [
                    '{../../classes/uuuuuuu.php}:{175}' => [
                        '../../classes/uuuuuuu.php',
                        '175',
                    ],
                ],
            ],
            $result[4]
        );
    }

    public function testPlurals(): void
    {
        $result = $this->parser->parse(\file_get_contents($this->fixturePath . '/plurals.po'));

        static::assertCount(7, $result['headers']);

        unset($result['headers']);

        static::assertCount(15, $result);
        static::assertSame(
            [
                'msgid'      => ['%s post not updated, somebody is editing it.'],
                'msgstr'     => [],
                'msgctxt'    => [],
                'ccomment'   => [],
                'tcomment'   => [],
                'obsolete'   => false,
                'fuzzy'      => false,
                'flags'      => [],
                'references' => [
                    '{wp-admin/edit.php}:{238}' => [
                        'wp-admin/edit.php',
                        '238',
                    ],
                ],
                'msgid_plural' => [
                    '%s posts not updated, somebody is editing them.',
                ],
                'msgstr[0]' => [
                    '%s entrada no actualizada',
                    ', alguien la está editando.',
                ],
                'msgstr[1]' => [
                    '%s entradas no actualizadas, alguien las está editando.',
                ],
            ],
            $result[0]
        );
    }

    public function testPluralsMultiline(): void
    {
        $result = $this->parser->parse(\file_get_contents($this->fixturePath . '/pluralsMultiline.po'));

        unset($result['headers']);

        static::assertCount(2, $result);

        foreach ($result as $id => $entry) {
            static::assertTrue(isset($entry['msgstr[0]']));
            static::assertTrue(isset($entry['msgstr[1]']));
        }
    }

    public function testNoBlankLines(): void
    {
        $result = $this->parser->parse(\file_get_contents($this->fixturePath . '/noblankline.po'));

        unset($result['headers']);

        $expected = [
            [
                'msgid'      => ['one'],
                'msgstr'     => ['uno'],
                'msgctxt'    => [],
                'ccomment'   => [],
                'tcomment'   => [],
                'obsolete'   => false,
                'fuzzy'      => false,
                'flags'      => [],
                'references' => [],
            ],
            [
                'msgid'      => ['two'],
                'msgstr'     => ['dos'],
                'msgctxt'    => [],
                'ccomment'   => [],
                'tcomment'   => [],
                'obsolete'   => false,
                'fuzzy'      => false,
                'flags'      => [],
                'references' => [],
            ],
        ];

        static::assertEquals($result, $expected);
    }

    public function testPreviousUnstranslated(): void
    {
        $result = $this->parser->parse(\file_get_contents($this->fixturePath . '/previous_unstranslated.po'));

        unset($result['headers']);

        $expected = [
            [
                'msgid'      => ['this is a string'],
                'msgstr'     => ['this is a translation'],
                'msgctxt'    => [],
                'ccomment'   => [],
                'tcomment'   => [],
                'obsolete'   => false,
                'fuzzy'      => false,
                'flags'      => [],
                'references' => [],
                'previous'   => [
                    'msgid'  => ['this is a previous string'],
                    'msgstr' => ['this is a previous translation string'],
                ],
            ],
        ];

        static::assertEquals($result, $expected);
    }

    public function testNoSpaceBetweenCommentAndMessage(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Parse error! Comments must have a space after them on line: [12].');

        $this->parser->parse(\file_get_contents($this->fixturePath . '/no_space_between_comment_and_space.po'));
    }

    public function testBrokenPoFile(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Parse error! Unknown key [msgida] on line: [0].');

        $this->parser->parse(\file_get_contents($this->fixturePath . '/broken.po'));
    }

    public function testDumpSimplePoFile(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/healthy.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        static::assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithNoHeader(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/noheader.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        static::assertEquals(\str_replace("\r", '', $fileContent . "\n"), $output);
    }

    public function testDumpPoFileWithMultilines(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/multilines.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        static::assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithContext(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/context.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        static::assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithPreviousUnstranslated(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/previous_unstranslated.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        static::assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithMultiflags(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/multiflags.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        static::assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithFlagsPhpformat(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/flags-phpformat.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        static::assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithFlagsPhpformatAndFuzzy(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/flags-phpformat-fuzzy.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        static::assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDisabledTranslations(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/disabled-translations.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        static::assertEquals($fileContent, $output);
    }
}
