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

namespace Viserio\Component\Config\Tests\Processor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Processor\CsvParameterProcessor;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Config\Processor\CsvParameterProcessor
 */
final class CsvParameterProcessorTest extends TestCase
{
    public const CONFIG_TEST = 'config';

    /** @var \Viserio\Component\Config\Processor\CsvParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new CsvParameterProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{\'\'|csv}'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('{\'\'|str_getcsv}'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['csv' => 'array', 'str_getcsv' => 'array'], CsvParameterProcessor::getProvidedTypes());
    }

    /**
     * @dataProvider provideProcessCases
     *
     * @param string $parameter
     * @param string $value
     *
     * @return void
     */
    public function testProcess($parameter, $value): void
    {
        self::assertSame($value, $this->processor->process($parameter . '|csv'));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function provideProcessCases(): iterable
    {
        $complex = <<<'CSV'
,"""","foo""","\""",\,foo\
CSV;

        return [
            ['', [null]],
            [',', ['', '']],
            ['1', ['1']],
            ['1,2," 3 "', ['1', '2', ' 3 ']],
            ['\\,\\\\', ['\\', '\\\\']],
            [$complex, ['', '"', 'foo"', '\\"', '\\', 'foo\\']],
        ];
    }
}
