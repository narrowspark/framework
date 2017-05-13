<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Formats\Qt;

class QtTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parsers\Formats\Qt
     */
    private $parser;

    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    private $file;

    /**
     * @var array
     */
    private $data;

    public function setUp()
    {
        $this->file   = new Filesystem();
        $this->parser = new Qt();
        $this->data   = [
            'contentstructuremenu/show_content_structure' => [
                [
                    'source'      => 'Node ID: %node_id Visibility: %visibility',
                    'translation' => [
                        'content'    => 'Knoop ID: %node_id Zichtbaar: %visibility',
                        'attributes' => false,
                    ],
                ],
            ],
            'design/admin/class/classlist' => [
                [
                    'source'      => '%group_name [Class group]',
                    'translation' => [
                        'content'    => '%group_name [Class groep]',
                        'attributes' => false,
                    ],
                ],
                [
                    'source'      => 'Select the item that you want to be the default selection and click "OK".',
                    'translation' => [
                        'content'    => '',
                        'attributes' => ['type' => 'unfinished'],
                    ],
                ],
            ],
            'design/admin/collaboration/group_tree' => [
                [
                    'source'      => 'Groups',
                    'translation' => [
                        'content'    => 'Groepen',
                        'attributes' => ['type' => 'obsolete'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers \Viserio\Component\Parsers\Formats\Qt::parse
     */
    public function testParse()
    {
        self::assertSame(
            $this->data,
            $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/qt/resources.ts'))
        );
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     * @expectedExceptionMessage Content does not contain valid XML, it is empty.
     */
    public function testParseWithEmptyContent()
    {
        $datas = $this->parser->parse('');
    }

    public function testDump()
    {
        self::assertXmlStringEqualsXmlFile(
            __DIR__ . '/../Fixtures/qt/resources.ts',
            $this->parser->dump($this->data)
        );
    }
}
