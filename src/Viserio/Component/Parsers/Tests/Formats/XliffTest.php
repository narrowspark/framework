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
            'welcome' => [
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

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     * @expectedExceptionMessage Content does not contain valid XML, it is empty.
     */
    public function testParseWithEmptyContent()
    {
        $datas = $this->parser->parse('');
    }
}
