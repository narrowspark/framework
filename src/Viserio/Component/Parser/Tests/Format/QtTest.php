<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Parser\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\QtDumper;
use Viserio\Component\Parser\Parser\QtParser;

/**
 * @internal
 *
 * @small
 */
final class QtTest extends TestCase
{
    /** @var array<string, mixed> */
    private $data;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->data = [
            'contentstructuremenu/show_content_structure' => [
                [
                    'source' => 'Node ID: %node_id Visibility: %visibility',
                    'translation' => [
                        'content' => 'Knoop ID: %node_id Zichtbaar: %visibility',
                        'attributes' => false,
                    ],
                ],
            ],
            'design/admin/class/classlist' => [
                [
                    'source' => '%group_name [Class group]',
                    'translation' => [
                        'content' => '%group_name [Class groep]',
                        'attributes' => false,
                    ],
                ],
                [
                    'source' => 'Select the item that you want to be the default selection and click "OK".',
                    'translation' => [
                        'content' => '',
                        'attributes' => ['type' => 'unfinished'],
                    ],
                ],
            ],
            'design/admin/collaboration/group_tree' => [
                [
                    'source' => 'Groups',
                    'translation' => [
                        'content' => 'Groepen',
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
            (new QtParser())->parse((string) \file_get_contents(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'qt' . \DIRECTORY_SEPARATOR . 'resources.ts'))
        );
    }

    public function testParseWithEmptyContent(): void
    {
        $this->expectException(\Viserio\Contract\Parser\Exception\ParseException::class);
        $this->expectExceptionMessage('Content does not contain valid XML, it is empty.');

        (new QtParser())->parse('');
    }

    public function testDump(): void
    {
        self::assertXmlStringEqualsXmlFile(
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'qt' . \DIRECTORY_SEPARATOR . 'resources.ts',
            (new QtDumper())->dump($this->data)
        );
    }
}
