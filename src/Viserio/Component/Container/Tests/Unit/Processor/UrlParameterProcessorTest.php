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
use Viserio\Component\Container\Processor\UrlParameterProcessor;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Container\Processor\UrlParameterProcessor
 *
 * @small
 */
final class UrlParameterProcessorTest extends TestCase
{
    /** @var \Viserio\Component\Container\Processor\UrlParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new UrlParameterProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{TEST|url}'));
        self::assertTrue($this->processor->supports('{TEST|query_string}'));
        self::assertFalse($this->processor->supports('test'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['url' => 'array', 'query_string' => 'array'], UrlParameterProcessor::getProvidedTypes());
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
     * @return array<int, array<int, array<string, null|string>|string>>
     */
    public static function provideProcessCases(): iterable
    {
        return [
            [
                'https://example.com/|url',
                [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'path' => null,
                    'port' => null,
                    'user' => null,
                    'pass' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                'https://example.com|url',
                [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'port' => null,
                    'user' => null,
                    'pass' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                'https://example.com/?query=test|query_string',
                [
                    'query' => 'test',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideProcessWithMissingFileCases
     *
     * @param string $parameter
     * @param string $message
     *
     * @return void
     */
    public function testProcessWithMissingFile(string $parameter, string $message): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        $this->processor->process($parameter);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function provideProcessWithMissingFileCases(): iterable
    {
        return [
            ['foo|url', 'Invalid URL parameter [foo|url]: schema and host expected, [foo] given.'],
        ];
    }

    public function testProcessWithInvalidProcessor(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported processor [foo] for [foo|foo] given.');

        $this->processor->process('foo|foo');
    }
}
