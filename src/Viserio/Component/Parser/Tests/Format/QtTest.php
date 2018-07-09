<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\QtDumper;
use Viserio\Component\Parser\Parser\QtParser;

/**
 * @internal
 */
final class QtTest extends TestCase
{
    /**
     * @var array
     */
    private $data;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
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
        static::assertSame(
            $this->data,
            (new QtParser())->parse(\file_get_contents(__DIR__ . '/../Fixture/qt/resources.ts'))
        );
    }

    public function testParseWithEmptyContent(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\ParseException::class);
        $this->expectExceptionMessage('Content does not contain valid XML, it is empty.');

        (new QtParser())->parse('');
    }

    public function testDump(): void
    {
        static::assertXmlStringEqualsXmlFile(
            __DIR__ . '/../Fixture/qt/resources.ts',
            (new QtDumper())->dump($this->data)
        );
    }
}
