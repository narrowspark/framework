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
            'config' => [
                AbstractKernel::class,
                'getConfigPath',
            ],
            'string' => __DIR__,
            'parameter' => 'foo',
        ];
    }

    public function testSupports(): void
    {
        $processor = new DirectoryParameterProcessor($this->data, $this->containerMock);

        self::assertTrue($processor->supports('{test|directory}'));
        self::assertFalse($processor->supports('test'));
    }

    public function testProcess(): void
    {
        $kernel = new Kernel();

        $this->containerMock->shouldReceive('has')
            ->once()
            ->with(AbstractKernel::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->once()
            ->andReturn($kernel);
        $this->containerMock->shouldReceive('hasParameter')
            ->once()
            ->with(__DIR__)
            ->andReturn(false);
        $this->containerMock->shouldReceive('hasParameter')
            ->once()
            ->with('foo')
            ->andReturn(true);
        $this->containerMock->shouldReceive('getParameter')
            ->once()
            ->with('foo')
            ->andReturn(__DIR__);


        $processor = new DirectoryParameterProcessor($this->data, $this->containerMock);

        self::assertSame($kernel->getConfigPath(), $processor->process('config|directory'));
        self::assertSame(__DIR__, $processor->process('string|directory'));
        self::assertSame(__DIR__, $processor->process('parameter|directory'));
    }

    public function testProcessToThrowExceptionIfMapperWasNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resolving of [{config|directory}/test] failed, no mapper was found');

        $processor = new DirectoryParameterProcessor($this->data, $this->containerMock);

        $processor->process('{config|directory}/test');
    }
}
