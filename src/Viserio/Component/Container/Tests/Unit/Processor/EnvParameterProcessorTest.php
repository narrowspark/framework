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
use Viserio\Component\Container\Processor\EnvParameterProcessor;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Container\Processor\EnvParameterProcessor
 *
 * @small
 */
final class EnvParameterProcessorTest extends TestCase
{
    private EnvParameterProcessor $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new EnvParameterProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{test|env}'));
        self::assertFalse($this->processor->supports('test'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['env' => 'bool|int|float|string|array'], EnvParameterProcessor::getProvidedTypes());
    }

    /**
     * @dataProvider provideProcessCases
     */
    public function testProcess(string $expected, string $key): void
    {
        $types = [
            '_ENV',
            '_SERVER',
            'putenv',
        ];

        foreach ($types as $type) {
            if ($type === '_ENV') {
                $_ENV[$key] = $expected;
            } elseif ($type === '_SERVER') {
                $_SERVER[$key] = $expected;
            } elseif ($type === 'putenv') {
                \putenv(\sprintf('%s=%s', $key, $expected));
            }

            self::assertSame($expected, $this->processor->process($key . '|env'));

            if ($type === '_ENV') {
                unset($_ENV[$key]);
            } elseif ($type === '_SERVER') {
                unset($_SERVER[$key]);
            } elseif ($type === 'putenv') {
                \putenv(\sprintf('%s=', $key));
                \putenv($key);
            }
        }
    }

    public static function provideProcessCases(): iterable
    {
        yield ['local', 'LOCAL'];

        yield ['bar', 'bar'];

        yield ['teststring', 'TEST_NORMAL'];

        yield ['foo', '"bar"'];

        yield ['TEST_QUOTES', '"teststring"'];
    }

    public function testProcessToThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No env value found for [NOT_SET|env].');

        $this->processor->process('NOT_SET|env');
    }
}
