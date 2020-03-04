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
use Viserio\Component\Container\Processor\PhpTypeParameterProcessor;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Container\Processor\PhpTypeParameterProcessor
 *
 * @small
 */
final class PhpTypeParameterProcessorTest extends TestCase
{
    /** @var \Viserio\Component\Container\Processor\PhpTypeParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new PhpTypeParameterProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{TEST|bool}'));
        self::assertTrue($this->processor->supports('{TEST|float}'));
        self::assertTrue($this->processor->supports('{TEST|int}'));
        self::assertTrue($this->processor->supports('{TEST|string}'));
        self::assertTrue($this->processor->supports('{TEST|trim}'));
        self::assertFalse($this->processor->supports('test'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame([
            'bool' => 'bool',
            'float' => 'float',
            'int' => 'int',
            'string' => 'string',
            'trim' => 'string',
        ], PhpTypeParameterProcessor::getProvidedTypes());
    }

    /**
     * @dataProvider provideProcessCases
     */
    public function testProcess(string $parameter, $value): void
    {
        self::assertSame($value, $this->processor->process($parameter));
    }

    /**
     * @return array<int, array<int, bool|float|int|string>>
     */
    public static function provideProcessCases(): iterable
    {
        return [
            ["hello\n|trim", 'hello'],
            ['1|float', 1.0],
            ['1.1|float', 1.1],
            ['1e1|float', 10.0],
            ['1|int', 1],
            ['1.1|int', 1],
            ['1e1|int', 10],
            ['true|bool', true],
            ['false|bool', false],
            ['null|bool', false],
            ['1|bool', true],
            ['0|bool', false],
            ['1.1|bool', true],
            ['1e1|bool', true],
            ['hello|string', 'hello'],
            ['true|string', 'true'],
            ['false|string', 'false'],
            ['null|string', 'null'],
            ['1|string', '1'],
            ['0|string', '0'],
            ['1.1|string', '1.1'],
            ['1e1|string', '1e1'],
        ];
    }

    /**
     * @dataProvider provideProcessWithInvalidCases
     */
    public function testProcessWithInvalid(string $parameter): void
    {
        $this->expectException(RuntimeException::class);

        $this->processor->process($parameter);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function provideProcessWithInvalidCases(): iterable
    {
        return [
            ['foo|float'],
            ['true|float'],
            ['null|float'],
            ['foo|int'],
            ['true|int'],
            ['null|int'],
        ];
    }

    public function testProcessWithInvalidProcessor(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported processor [foo] for [foo|foo] given.');

        $this->processor->process('foo|foo');
    }
}
