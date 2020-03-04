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
use Viserio\Component\Container\Processor\Base64ParameterProcessor;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Container\Processor\Base64ParameterProcessor
 *
 * @small
 */
final class Base64ParameterProcessorTest extends TestCase
{
    public const CONFIG_TEST = 'config';

    /** @var \Viserio\Component\Container\Processor\Base64ParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new Base64ParameterProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{W10=|base64}'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('{W10=|base64_decode}'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['base64' => 'string', 'base64_decode' => 'string'], Base64ParameterProcessor::getProvidedTypes());
    }

    /**
     * @dataProvider provideProcessCases
     *
     * @param string $value
     */
    public function testProcess($value, string $parameter): void
    {
        self::assertSame($value, $this->processor->process($parameter));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function provideProcessCases(): iterable
    {
        return [
            ['[]', 'W10=|base64'],
            ['[]', 'W10=|base64_decode'],
        ];
    }

    /**
     * @dataProvider provideProcessWithInvalidCases
     */
    public function testProcessWithInvalid(string $key, string $parameter): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('Base64 decoding of [%s] failed, on given parameter [%s].', $key, $parameter));

        $this->processor->process($parameter);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function provideProcessWithInvalidCases(): iterable
    {
        return [
            ["\xFF\xED", "\xFF\xED|base64"],
        ];
    }
}
