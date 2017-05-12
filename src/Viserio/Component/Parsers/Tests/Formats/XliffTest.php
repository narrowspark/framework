<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Formats\Xliff;

class XliffTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parsers\Formats\Xliff
     */
    private $parser;

    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file   = new Filesystem();
        $this->parser = new Xliff();
    }

    public function testParseXliffV1()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/xliffv1.xlf'));

        self::assertSame(unserialize($this->file->read(__DIR__ . '/../Fixtures/xliff/output_xliffv1.xlf')), $datas);
    }

    public function testParseXliffV1WithEmptySource()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/translated.xlf'));

        self::assertSame([
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

    public function testParseXliffV2()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/xliffv2.xlf'));

        self::assertSame(unserialize($this->file->read(__DIR__ . '/../Fixtures/xliff/output_xliffv2.xlf')), $datas);
    }

    public function testParseEncodingV1()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/encoding_xliff_v1.xlf'));

        self::assertSame([
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

    public function testParseEncodingV2()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/encoding_xliff_v2.xlf'));

        self::assertSame([
            'version' => '2.0',
            'srcLang' => 'en-US',
            'trgLang' => 'de-CH',
            'foo'     => [
                'source' => 'foo',
                'target' => 'bär',
            ],
            'bar' => [
                'source' => 'bar',
                'target' => 'föö',
            ],
        ], $datas);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     */
    public function testParseXliffV1NoVersion()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/xliff_no_version.xlf'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     * @expectedExceptionMessage No support implemented for loading XLIFF version "3.0".
     */
    public function testParseXliffV1NoVersionAndNamespace()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/xliff_no_version_and_namespace.xlf'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     * @expectedExceptionMessage Not a valid XLIFF namespace "urn:oasis:names:tc:xliff:"
     */
    public function testParseXliffV1NoVersionAndInvalidNamespace()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/xliff_no_version_and_invalid_namespace.xlf'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     */
    public function testParseXliffV1NoVersionAndNoNamespace()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/xliff_no_version_and_no_namespace.xlf'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     * @expectedExceptionMessage Content does not contain valid XML, it is empty.
     */
    public function testParseWithEmptyContent()
    {
        $datas = $this->parser->parse('');
    }

    public function testDumpXliffV2()
    {
        $datas = [
            'version' => '2.0',
            'srcLang' => 'en-US',
            'trgLang' => 'de-CH',
            'foo'     => [
                'source' => 'foo',
                'target' => 'bär',
            ],
            'bar' => [
                'source' => 'bar',
                'target' => 'föö',
            ],
        ];

        self::assertXmlStringEqualsXmlFile(
            __DIR__ . '/../Fixtures/xliff/encoding_xliff_v2.xlf',
            $this->parser->dump($datas)
        );
    }

    public function testDumpXliffV1()
    {
        $datas = [
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
        ];

        self::assertXmlStringEqualsXmlFile(
            __DIR__ . '/../Fixtures/xliff/encoding_xliff_v1_utf8.xlf',
            $this->parser->dump($datas)
        );
    }
}
