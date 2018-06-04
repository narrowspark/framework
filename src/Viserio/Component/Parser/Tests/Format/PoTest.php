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

            $this->fail($e->getMessage());
        }

        $this->assertCount(3, $result);
        $this->assertArrayHasKey('headers', $result);
        $this->assertSame(
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
        $this->assertSame(
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
            $this->fail($e->getMessage());
        }

        $this->assertCount(3, $result, 'Did not read properly po file without headers.');
        $this->assertCount(0, $result['headers']);
    }

    public function testHeaders(): void
    {
        $result  = $this->parser->parse(\file_get_contents($this->fixturePath . '/healthy.po'));
        $headers = $result['headers'];

        $this->assertCount(18, $headers);
        $this->assertSame('', $headers['Project-Id-Version']);
        $this->assertSame('', $headers['Report-Msgid-Bugs-To']);
        $this->assertSame('2017-09-28 15:55+0100', $headers['POT-Creation-Date']);
        $this->assertSame('', $headers['PO-Revision-Date']);
        $this->assertSame('Narrowspark <EMAIL@ADDRESS>', $headers['Last-Translator']);
        $this->assertSame('', $headers['Language-Team']);
        $this->assertSame('1.0', $headers['MIME-Version']);
        $this->assertSame('text/plain; charset=UTF-8', $headers['Content-Type']);
        $this->assertSame('8bit', $headers['Content-Transfer-Encoding']);
        $this->assertSame('nplurals=2; plural=n != 1;', $headers['Plural-Forms']);
        $this->assertSame('UTF-8', $headers['X-Poedit-SourceCharset']);
        $this->assertSame('__;_e;_n;_t', $headers['X-Poedit-KeywordsList']);
        $this->assertSame('yes', $headers['X-Textdomain-Support']);
        $this->assertSame('.', $headers['X-Poedit-Basepath']);
        $this->assertSame('Poedit 2.0.4', $headers['X-Generator']);
        $this->assertSame('.', $headers['X-Poedit-SearchPath-0']);
        $this->assertSame('../..', $headers['X-Poedit-SearchPath-1']);
        $this->assertSame('../../../modules', $headers['X-Poedit-SearchPath-2']);
    }

    public function testMultiLinesHeaders(): void
    {
        $result  = $this->parser->parse(\file_get_contents($this->fixturePath . '/multiline-header.po'));
        $headers = $result['headers'];

        $this->assertCount(18, $headers);
        $this->assertSame('', $headers['Project-Id-Version']);
        $this->assertSame('', $headers['Report-Msgid-Bugs-To']);
        $this->assertSame('2017-09-28 15:55+0100', $headers['POT-Creation-Date']);
        $this->assertSame('', $headers['PO-Revision-Date']);
        $this->assertSame('Narrowspark <EMAIL@ADDRESS>', $headers['Last-Translator']);
        $this->assertSame('', $headers['Language-Team']);
        $this->assertSame('1.0', $headers['MIME-Version']);
        $this->assertSame('text/plain; charset=UTF-8', $headers['Content-Type']);
        $this->assertSame('8bit', $headers['Content-Transfer-Encoding']);
        // a multi-line header value
        $this->assertSame(
            [
                'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n',
                '%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);',
            ],
            $headers['Plural-Forms']
        );
        $this->assertSame('UTF-8', $headers['X-Poedit-SourceCharset']);
        $this->assertSame('__;_e;_n;_t', $headers['X-Poedit-KeywordsList']);
        $this->assertSame('yes', $headers['X-Textdomain-Support']);
        $this->assertSame('.', $headers['X-Poedit-Basepath']);
        $this->assertSame('Poedit 2.0.4', $headers['X-Generator']);
        $this->assertSame('.', $headers['X-Poedit-SearchPath-0']);
        $this->assertSame('../..', $headers['X-Poedit-SearchPath-1']);
        $this->assertSame('../../../modules', $headers['X-Poedit-SearchPath-2']);
    }

    public function testMultiLineId(): void
    {
        $result = $this->parser->parse(\file_get_contents($this->fixturePath . '/multilines.po'));

        $this->assertSame(
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

        $this->assertSame(
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
        $this->assertSame(
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

        $this->assertCount(7, $result['headers']);

        unset($result['headers']);

        $this->assertCount(15, $result);
        $this->assertSame(
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

        $this->assertCount(2, $result);

        foreach ($result as $id => $entry) {
            $this->assertTrue(isset($entry['msgstr[0]']));
            $this->assertTrue(isset($entry['msgstr[1]']));
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

        $this->assertEquals($result, $expected);
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

        $this->assertEquals($result, $expected);
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

        $this->assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithNoHeader(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/noheader.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        $this->assertEquals(\str_replace("\r", '', $fileContent . "\n"), $output);
    }

    public function testDumpPoFileWithMultilines(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/multilines.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        $this->assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithContext(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/context.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        $this->assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithPreviousUnstranslated(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/previous_unstranslated.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        $this->assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithMultiflags(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/multiflags.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        $this->assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithFlagsPhpformat(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/flags-phpformat.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        $this->assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDumpPoFileWithFlagsPhpformatAndFuzzy(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/flags-phpformat-fuzzy.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        $this->assertEquals(\str_replace("\r", '', $fileContent), $output);
    }

    public function testDisabledTranslations(): void
    {
        $fileContent = \file_get_contents($this->fixturePath . '/disabled-translations.po');
        $result      = $this->parser->parse($fileContent);
        $output      = $this->dumper->dump($result);

        $this->assertEquals($fileContent, $output);
    }
}
