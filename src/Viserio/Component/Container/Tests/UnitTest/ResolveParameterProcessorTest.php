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

namespace Viserio\Component\Container\Tests\UnitTest;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Viserio\Component\Container\Processor\ResolveParameterProcessor;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 */
final class ResolveParameterProcessorTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\Container\CompiledContainer */
    private $containerMock;

    /** @var \Viserio\Component\Container\Processor\ResolveParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerMock = $this->mock(CompiledContainerContract::class);
        $this->processor = new ResolveParameterProcessor($this->containerMock);
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['resolve' => 'string'], ResolveParameterProcessor::getProvidedTypes());
    }

    public function testSupport(): void
    {
        self::assertTrue($this->processor->supports('{foo|resolve}'));
        self::assertFalse($this->processor->supports('{foo|foo}'));
    }

    public function testProcess(): void
    {
        $this->containerMock->shouldReceive('getParameter')
            ->with('foo')
            ->andReturn('test');

        $value = $this->processor->process('foo|resolve');

        self::assertSame('test', $value);
    }

    public function testProcessThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Parameter [foo] found when resolving env var [foo|resolve] must be scalar, [object] given.');

        $this->containerMock->shouldReceive('getParameter')
            ->with('foo')
            ->andReturn(new stdClass());

        $this->processor->process('foo|resolve');
    }
}
