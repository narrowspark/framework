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

namespace Viserio\Component\Container\Tests\Processor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Processor\JsonParameterProcessor;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Container\Processor\JsonParameterProcessor
 *
 * @small
 */
final class JsonParameterProcessorTest extends TestCase
{
    /** @var \Viserio\Component\Container\Processor\JsonParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new JsonParameterProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{TEST|json}'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('{TEST|json_decode}'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['json' => 'array', 'json_decode' => 'array'], JsonParameterProcessor::getProvidedTypes());
    }

    /**
     * @dataProvider provideProcessCases
     *
     * @param string $parameter
     * @param mixed  $value
     *
     * @return void
     */
    public function testProcess(string $parameter, $value): void
    {
        self::assertSame($value, $this->processor->process($parameter));
    }

    /**
     * @return array<int, array<int, array<int|string, int|string>|string>>
     */
    public static function provideProcessCases(): iterable
    {
        return [
            ['[1]|json', [1]],
            ['{"key": "value"}|json', ['key' => 'value']],

            ['[1]|json_decode', [1]],
            ['{"key": "value"}|json_decode', ['key' => 'value']],
        ];
    }

    /**
     * @dataProvider provideProcessWithInvalidJsonCases
     *
     * @param string $parameter
     * @param mixed  $json
     *
     * @return void
     */
    public function testProcessWithInvalidJson(string $parameter, $json): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('Invalid JSON env var [%s]: array or null expected, [%s] given.', $json, \gettype($json)));

        $this->processor->process($parameter);
    }

    /**
     * @return array<int, array<int, bool|float|int|string>>
     */
    public static function provideProcessWithInvalidJsonCases(): iterable
    {
        return [
            ['1|json', 1],
            ['1.1|json', 1.1],
            ['true|json', true],
            ['false|json', false],
        ];
    }

    public function testProcessWithSyntaxError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON in parameter ["foo",|json]: Syntax error.');

        $this->processor->process('"foo",|json');
    }
}
