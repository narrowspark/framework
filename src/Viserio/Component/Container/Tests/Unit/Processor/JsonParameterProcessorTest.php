<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
