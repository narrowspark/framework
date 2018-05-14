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

namespace Viserio\Component\Foundation\Tests\Config\Processor;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Config\Processor\DirectoryProcessor;
use Viserio\Component\Foundation\Console\Kernel;

/**
 * @internal
 *
 * @small
 */
final class DirectoryProcessorTest extends MockeryTestCase
{
    /**
     * Container instance.
     *
     * @var \Mockery\MockInterface|\Psr\Container\ContainerInterface
     */
    protected $containerMock;

    /** @var \Viserio\Component\Foundation\Config\Processor\DirectoryProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerMock = \Mockery::mock(ContainerInterface::class);
        $this->processor = new DirectoryProcessor(['viserio' => ['config' => ['processor' => [DirectoryProcessor::getReferenceKeyword() => ['mapper' => ['config' => [AbstractKernel::class, 'getConfigPath']]]]]]], $this->containerMock);
    }

    public function testGetReferenceKeyword(): void
    {
        self::assertSame('directory', DirectoryProcessor::getReferenceKeyword());
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('%' . DirectoryProcessor::getReferenceKeyword() . ':test%'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('%' . DirectoryProcessor::getReferenceKeyword() . ':config-dir%/test'));
    }

    public function testProcess(): void
    {
        $kernel = new Kernel();

        $this->containerMock->shouldReceive('has')
            ->twice()
            ->with(AbstractKernel::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->twice()
            ->andReturn($kernel);

        self::assertSame($kernel->getConfigPath(), $this->processor->process('%' . DirectoryProcessor::getReferenceKeyword() . ':config%'));
        self::assertSame($kernel->getConfigPath('test'), $this->processor->process('%' . DirectoryProcessor::getReferenceKeyword() . ':config%' . \DIRECTORY_SEPARATOR . 'test'));
        self::assertSame('%' . DirectoryProcessor::getReferenceKeyword() . ':test%', $this->processor->process('%' . DirectoryProcessor::getReferenceKeyword() . ':test%'));
    }
}
