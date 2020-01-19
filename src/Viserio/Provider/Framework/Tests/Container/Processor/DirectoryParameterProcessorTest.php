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

namespace Viserio\Provider\Framework\Tests\Container\Processor;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Console\Kernel;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Container\Processor\AbstractParameterProcessor
 * @covers \Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor
 */
final class DirectoryParameterProcessorTest extends MockeryTestCase
{
    /**
     * Container instance.
     *
     * @var \Mockery\MockInterface|\Viserio\Contract\Container\CompiledContainer
     */
    protected $containerMock;

    /** @var array */
    private array $data;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerMock = Mockery::mock(CompiledContainerContract::class);
        $this->data = [
            'viserio' => [
                'container' => [
                    'processor' => [
                        'directory' => [
                            'mapper' => [
                                'config' => [
                                    AbstractKernel::class,
                                    'getConfigPath',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testSupports(): void
    {
        $key = 'config.directory.processor.check_strict';

        $this->containerMock->shouldReceive('hasParameter')
            ->once()
            ->with($key)
            ->andReturn(true);
        $this->containerMock->shouldReceive('getParameter')
            ->once()
            ->with($key)
            ->andReturn(true);

        $processor = new DirectoryParameterProcessor($this->containerMock);

        self::assertTrue($processor->supports('{test|directory}'));
        self::assertFalse($processor->supports('test'));
    }

    public function testProcessWithoutStrictMode(): void
    {
        $kernel = new Kernel();

        $key = 'config.directory.processor.check_strict';

        $this->containerMock->shouldReceive('hasParameter')
            ->once()
            ->with($key)
            ->andReturn(false);
        $this->containerMock->shouldReceive('has')
            ->once()
            ->with($key)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->once()
            ->with($key)
            ->andReturn(false);

        $this->containerMock->shouldReceive('hasParameter')
            ->twice()
            ->with(AbstractKernel::class)
            ->andReturn(false);
        $this->containerMock->shouldReceive('has')
            ->twice()
            ->with(AbstractKernel::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->twice()
            ->andReturn($kernel);

        $processor = new DirectoryParameterProcessor($this->containerMock);

        self::assertSame($kernel->getConfigPath(), $processor->process('config|directory'));
        self::assertSame($kernel->getConfigPath('test'), $processor->process('{config|directory}' . \DIRECTORY_SEPARATOR . 'test'));
        self::assertSame('test|directory', $processor->process('test|directory'));
    }

    public function testProcessWithStrictMode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resolving of [%directory:test%] failed, no mapper was found.');

        $key = 'config.directory.processor.check_strict';

        $this->containerMock->shouldReceive('hasParameter')
            ->once()
            ->with($key)
            ->andReturn(true);
        $this->containerMock->shouldReceive('getParameter')
            ->once()
            ->with($key)
            ->andReturn(true);

        $processor = new DirectoryParameterProcessor($this->containerMock);

        self::assertSame(':test%', $processor->process(':test%'));
    }
}
