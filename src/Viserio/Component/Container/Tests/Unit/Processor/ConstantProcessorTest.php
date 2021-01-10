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
use Viserio\Component\Container\Processor\ConstantProcessor;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Container\Processor\ConstantProcessor
 *
 * @small
 */
final class ConstantProcessorTest extends TestCase
{
    public const CONFIG_TEST = 'config';

    /** @var \Viserio\Component\Container\Processor\ConstantProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new ConstantProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{TEST|const}'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('{ContainerDir|const}/test'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['const' => 'bool|int|float|string|array'], ConstantProcessor::getProvidedTypes());
    }

    /**
     * @dataProvider provideProcessCases
     */
    public function testProcess(string $constantString, $constantValue): void
    {
        self::assertSame($constantValue, $this->processor->process($constantString . '|const'));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function provideProcessCases(): iterable
    {
        if (! \defined('CONFIG_PROCESSOR_CONSTANT_TEST')) {
            \define('CONFIG_PROCESSOR_CONSTANT_TEST', 'test-key');
        }

        return [
            'constant' => ['CONFIG_PROCESSOR_CONSTANT_TEST', CONFIG_PROCESSOR_CONSTANT_TEST],
            'class-constant' => [__CLASS__ . '::CONFIG_TEST', self::CONFIG_TEST],
            'class-pseudo-constant' => [__CLASS__ . '::class', self::class],
            'class-pseudo-constant-upper' => [__CLASS__ . '::CLASS', self::class],
        ];
    }

    /**
     * @dataProvider provideProcessWithInvalidConstantsCases
     */
    public function testProcessWithInvalidConstants(string $parameter): void
    {
        $parameter = $parameter . '|const';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Constant for [%s] was not found.', $parameter));

        $this->processor->process($parameter);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function provideProcessWithInvalidConstantsCases(): iterable
    {
        return [
            ['\Tests\EnvVarProcessorTest::UNDEFINED_CONST'],
            ['UNDEFINED_CONST'],
        ];
    }

    public function testProcessWithoutUserConstants(): void
    {
        $processor = new ConstantProcessor(false);

        self::assertSame(\PHP_BINARY, $processor->process('PHP_BINARY|const'));
    }
}
