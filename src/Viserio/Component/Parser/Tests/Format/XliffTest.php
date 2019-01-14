<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Parser\Exception\DumpException;
use Viserio\Component\Contract\Parser\Exception\ParseException;
use Viserio\Component\Parser\Dumper\XliffDumper;
use Viserio\Component\Parser\Parser\XliffParser;

/**
 * @internal
 */
final class XliffTest extends TestCase
{
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

        $this->fixturePath = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture';
    }

    public function testParseXliffV1(): void
    {
        $datas = $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'xliffv1.xlf');

        $excepted = include $this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'output_xliffv1.php';

        $this->assertEquals($excepted, $datas);
    }

    public function testParseXliffV1WithEmptySource(): void
    {
        $datas = $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'translated.xlf');

        $this->assertSame([
            'version'         => '1.2',
            'source-language' => 'en',
            'target-language' => 'de-AT',
            'welcome'         => [
                'source' => 'Hooray, you\'re here! The day just got better - enjoy the following tips!',
                'target' => 'Hurra, du bist hier! Der Tag ist gerettet - nutze die folgenden Tipps!',
                'id'     => '1',
            ],
            'text_segment' => [
                'source' => 'A section of text like this is known as a text segment. Start rockin\' your translations now!',
                'target' => 'Eine Textpassage wie diese bezeichnen wir als Textsegment. Starte jetzt mit deinen Übersetzungen durch!',
                'id'     => '2',
            ],
            'tab_shortcut' => [
                'source' => 'Arriba, Arriba! Andale, Andale! Be fast as Speedy Gonzales. Just hit TAB to save and go to the next text segment, once you\'re done.',
                'target' => 'Arriba, Arriba! Andale, Andale! Sei schneller als Speedy Gonzales. Springe mit TAB ins nächste Textsegment, sobald du fertig bist. Deine Änderungen werden automatisch gespeichert.',
                'id'     => '3',
            ],
            'statuses' => [
                'source' => 'Houston, we have no problem. Keep track of the progress of your translations by statuses at any time.',
                'target' => '',
                'id'     => '4',
            ],
            'status_shortcut' => [
                'source' => 'Keep your fingers off the mouse. Master your keyboard: Change the status by using one of the shortcut keys: e.g. CTRL+K = Translated.    You can see other shortcuts by pressing CTRL+H!',
                'target' => '',
                'id'     => '5',
            ],
            'placeholder_lingochecks' => [
                'source' => "We just like to see you happy, that's why LingoChecks automatically check translations for predetermined criteria.    Among other checks LingoHub verifies if used in the original text are also present in translated texts.",
                'target' => 'Bazinga!',
                'id'     => '6',
            ],
            'comments' => [
                'source' => 'Dear developers, you are the masters of translation files. Add comments in a file (depends on file format) to provide translators with more information. They are imported as a description, visible in the side panel.',
                'target' => '',
                'id'     => '8',
                'notes'  => [
                    [
                        'content' => 'This is an awesome description.',
                    ],
                ],
            ],
            'love' => [
                'source' => 'Made with ❤',
                'target' => '❤',
                'id'     => '9',
                'notes'  => [
                    [
                        'content' => 'lh-check { min: 10, max: 15 }',
                    ],
                ],
            ],
        ], $datas);
    }

    public function testParseXliffV2(): void
    {
        $datas = $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'xliffv2.xlf');

        $this->assertSame(\unserialize(\file_get_contents($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'output_xliffv2.xlf')), $datas);
    }

    public function testParseEncodingV1(): void
    {
        $datas = $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'encoding_xliff_v1.xlf');

        $this->assertSame([
            'version'         => '1.2',
            'source-language' => 'en',
            'target-language' => '',
            'foo'             => [
                'source' => 'foo',
                'target' => 'bär',
                'id'     => '1',
                'notes'  => [
                    [
                        'content' => 'bäz',
                    ],
                ],
            ],
            'bar' => [
                'source' => 'bar',
                'target' => 'föö',
                'id'     => '2',
            ],
        ], $datas);
    }

    public function testParseEncodingV2(): void
    {
        $datas = $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'encoding_xliff_v2.xlf');

        $this->assertSame([
            'version' => '2.0',
            'srcLang' => 'en-US',
            'trgLang' => 'de-CH',
            'key1'    => [
                'source' => 'foo',
                'target' => 'bär',
            ],
            'key2' => [
                'source' => 'bar',
                'target' => 'föö',
            ],
        ], $datas);
    }

    public function testParseXliffV1NoVersion(): void
    {
        $this->expectException(ParseException::class);

        $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'xliff_no_version.xlf');
    }

    public function testParseXliffV1NoVersionAndNamespace(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Invalid resource provided: [1.2]; Errors: [ERROR 1845] Element \'{urn:oasis:names:tc:xliff:document:3.0}xliff\': No matching global declaration available for the validation root. (in ');

        $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'xliff_no_version_and_namespace.xlf');
    }

    public function testParseXliffV1NoVersionAndInvalidNamespace(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Invalid resource provided: [1.2]; Errors: [ERROR 1845] Element \'{urn:oasis:names:tc:xliff:}xliff\': No matching global declaration available for the validation root. (in');

        $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'xliff_no_version_and_invalid_namespace.xlf');
    }

    public function testParseXliffV1NoVersionAndNoNamespace(): void
    {
        $this->expectException(ParseException::class);

        $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'xliff_no_version_and_no_namespace.xlf');
    }

    public function testParseWithEmptyContent(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Content does not contain valid XML, it is empty.');

        (new XliffParser())->parse('');
    }

    public function testDumpXliffV1(): void
    {
        $datas = [
            'version'         => '1.2',
            'source-language' => 'en',
            'target-language' => 'de-CH',
            'encoding'        => 'UTF-8',
            'foo'             => [
                'source' => 'foo',
                'target' => 'bär',
                'id'     => '1',
                'notes'  => [
                    [
                        'content'  => 'bäz',
                        'from'     => 'daniel',
                        'priority' => '1',
                    ],
                ],
            ],
            'bar' => [
                'source'            => 'bar',
                'target'            => 'föö',
                'id'                => '2',
                'target-attributes' => [
                    'order' => '1',
                ],
            ],
            'key.with.cdata' => [
                'source' => 'key.with.cdata',
                'target' => '<source> & <target>',
            ],
        ];

        $this->assertXmlStringEqualsXmlFile(
            $this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'encoding_xliff_v1_utf8.xlf',
            (new XliffDumper())->dump($datas)
        );
    }

    public function testDumpXliffV2(): void
    {
        $datas = [
            'version'  => '2.0',
            'srcLang'  => 'en-US',
            'trgLang'  => 'de-CH',
            'encoding' => 'UTF-8',
            'key1'     => [
                'source' => 'foo',
                'target' => 'bär',
            ],
            'key2' => [
                'source'            => 'bar',
                'target'            => 'föö',
                'target-attributes' => [
                    'order' => '1',
                ],
            ],
            'key.with.cdata' => [
                'source' => 'cdata',
                'target' => '<source> & <target>',
            ],
        ];

        $this->assertXmlStringEqualsXmlFile(
            $this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'encoding_xliff_v2_utf8.xlf',
            (new XliffDumper())->dump($datas)
        );
    }

    public function testDumpXliffV2WithNotes(): void
    {
        $datas = [
            'version'  => '2.0',
            'srcLang'  => 'en-US',
            'trgLang'  => 'de-CH',
            'encoding' => 'UTF-8',
            'key1'     => [
                'source' => 'foo',
                'target' => 'bar',
                'notes'  => [
                    [
                        'category' => 'state',
                        'content'  => 'new',
                    ],
                    [
                        'category' => 'approved',
                        'content'  => 'true',
                    ],
                    [
                        'category' => 'section',
                        'content'  => 'user login',
                        'priority' => '1',
                    ],
                ],
            ],
        ];

        $this->assertXmlStringEqualsXmlFile(
            $this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'xliffv2-notes-meta.xlf',
            (new XliffDumper())->dump($datas)
        );
    }

    public function testParserXliffV2WithNotes(): void
    {
        $datas = $this->parseFile($this->fixturePath . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'xliffv2-notes-meta.xlf');

        $exceptedDatas = [
            'version' => '2.0',
            'srcLang' => 'en-US',
            'trgLang' => 'de-CH',
            'key1'    => [
                'source' => 'foo',
                'target' => 'bar',
                'notes'  => [
                    [
                        'category' => 'state',
                        'content'  => 'new',
                    ],
                    [
                        'category' => 'approved',
                        'content'  => 'true',
                    ],
                    [
                        'category' => 'section',
                        'priority' => '1',
                        'content'  => 'user login',
                    ],
                ],
            ],
        ];

        $this->assertSame($exceptedDatas, $datas);
    }

    public function testDumpWithWrongVersion(): void
    {
        $this->expectException(DumpException::class);
        $this->expectExceptionMessage('No support implemented for dumping XLIFF version [3.0].');

        (new XliffDumper())->dump(['version' => '3.0']);
    }

    /**
     * @param string $path
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\ParseException
     *
     * @return array
     */
    private function parseFile(string $path): array
    {
        return (new XliffParser())->parse(\file_get_contents($path));
    }
}
