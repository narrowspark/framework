<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parser\Dumper\QtDumper;
use Viserio\Component\Parser\Parser\QtParser;

class QtTest extends TestCase
{
    /**
     * @var \Viserio\Component\Contract\Filesystem\Filesystem
     */
    private $file;

    /**
     * @var array
     */
    private $data;

    public function setUp(): void
    {
        $this->file = new Filesystem();
        $this->data = [
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

    public function testParse(): void
    {
        self::assertSame(
            $this->data,
            (new QtParser())->parse((string) $this->file->read(__DIR__ . '/../Fixtures/qt/resources.ts'))
        );
    }

    /**
     * @expectedException \Viserio\Component\Contract\Parser\Exception\ParseException
     * @expectedExceptionMessage Content does not contain valid XML, it is empty.
     */
    public function testParseWithEmptyContent(): void
    {
        (new QtParser())->parse('');
    }

    public function testDump(): void
    {
        self::assertXmlStringEqualsXmlFile(
            __DIR__ . '/../Fixtures/qt/resources.ts',
            (new QtDumper())->dump($this->data)
        );
    }
}
