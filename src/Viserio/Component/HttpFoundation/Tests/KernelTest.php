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

namespace Viserio\Component\HttpFoundation\Tests;

use Exception;
use Mockery as Mock;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface as CompiledContainerContract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFoundation\Event\KernelExceptionEvent;
use Viserio\Component\HttpFoundation\Event\KernelFinishRequestEvent;
use Viserio\Component\HttpFoundation\Event\KernelRequestEvent;
use Viserio\Component\HttpFoundation\Event\KernelTerminateEvent;
use Viserio\Component\HttpFoundation\Kernel;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Contract\Foundation\BootstrapManager as BootstrapManagerContract;
use Viserio\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @small
 */
final class KernelTest extends MockeryTestCase
{
    /** @var string */
    private $routeCachePath;

    /** @var \Mockery\MockInterface|\Psr\Container\ContainerInterface */
    private $containerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $fixturePath = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture';

        $this->routeCachePath = $fixturePath . \DIRECTORY_SEPARATOR . 'storage' . \DIRECTORY_SEPARATOR . 'framework' . \DIRECTORY_SEPARATOR . 'routes.cache.php';
        $this->containerMock = Mock::mock(CompiledContainerContract::class);
    }

    public function testHttpHandle(): void
    {
        $serverRequest = $this->arrangeServerRequestWithXPhpObLevel();

        $this->containerMock->shouldReceive('set')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $events = $this->arrangeKernelHandleEvents();

        $this->arrangeContainerEventsCalls($this->containerMock, $events);

        $this->containerMock->shouldReceive('set')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $router = Mock::mock(RouterContract::class);
        $router->shouldReceive('dispatch')
            ->once()
            ->with(Mock::type(ServerRequestInterface::class))
            ->andReturn(Mock::mock(ResponseInterface::class));

        $this->arrangeDispatcher($this->containerMock);

        $this->containerMock->shouldReceive('get')
            ->once()
            ->with(RouterContract::class)
            ->andReturn($router);

        $kernel = $this->getKernel($this->containerMock);
        $kernel->setEnv('local');

        self::assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testHandleWithException(): void
    {
        $exception = new Exception('test');

        $serverRequest = $this->arrangeServerRequestWithXPhpObLevel();

        $router = Mock::mock(RouterContract::class);
        $router->shouldReceive('dispatch')
            ->once()
            ->with($serverRequest)
            ->andThrow($exception);

        $this->containerMock->shouldReceive('get')
            ->once()
            ->with(RouterContract::class)
            ->andReturn($router);

        $this->arrangeExceptionHandler($exception, $serverRequest, $this->containerMock);
        $this->arrangeDispatcher($this->containerMock);

        $events = $this->arrangeKernelHandleEvents();
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelExceptionEvent::class));

        $this->arrangeContainerEventsCalls($this->containerMock, $events);

        $this->containerMock->shouldReceive('set')
            ->twice()
            ->with(ServerRequestInterface::class, $serverRequest);

        $kernel = $this->getKernel($this->containerMock);
        $kernel->setEnv('local');

        self::assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testTerminate(): void
    {
        $responseMock = Mock::mock(ResponseInterface::class);
        $serverRequestMock = Mock::mock(ServerRequestInterface::class);

        $eventsMock = Mock::mock(EventManagerContract::class);
        $eventsMock->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelTerminateEvent::class));

        $this->arrangeContainerEventsCalls($this->containerMock, $eventsMock);

        /** @var \Mockery\MockInterface|\Viserio\Contract\Foundation\BootstrapManager $bootstrapManager */
        $bootstrapManager = Mock::mock(BootstrapManagerContract::class);

        $bootstrapManager->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(false);

        $bootstrapManager->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(true);

        $kernel = $this->getKernel($this->containerMock, $bootstrapManager);

        // Without bootstrap
        $kernel->terminate($serverRequestMock, $responseMock);

        $kernel->terminate($serverRequestMock, $responseMock);
    }

    /**
     * @param \Mockery\MockInterface|\Psr\Container\ContainerInterface     $container
     * @param \Mockery\MockInterface|\Viserio\Contract\Events\EventManager $events
     */
    protected function arrangeContainerEventsCalls(MockInterface $container, MockInterface $events): void
    {
        $container->shouldReceive('has')
            ->once()
            ->with(EventManagerContract::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->once()
            ->with(EventManagerContract::class)
            ->andReturn($events);
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    /**
     * @param \Mockery\MockInterface|\Psr\Container\ContainerInterface $container
     * @param null|\Viserio\Contract\Foundation\BootstrapManager       $bootstrapManager
     *
     * @return \Viserio\Component\HttpFoundation\Kernel
     */
    private function getKernel(MockInterface $container, ?BootstrapManagerContract $bootstrapManager = null): Kernel
    {
        $kernel = new class($container, $bootstrapManager) extends Kernel {
            public function __construct($container, $bootstrapManager)
            {
                $this->container = $container;

                parent::__construct();

                if ($bootstrapManager !== null) {
                    $this->bootstrapManager = $bootstrapManager;
                }
            }

            public function setEnv(string $env): void
            {
                $this->environment = $env;
            }

            /**
             * {@inheritdoc}
             */
            public function getRootDir(): string
            {
                return $this->rootDir = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture';
            }
        };

        return $kernel;
    }

    /**
     * @return \Mockery\MockInterface
     */
    private function arrangeKernelHandleEvents(): MockInterface
    {
        $eventsMock = Mock::mock(EventManagerContract::class);
        $eventsMock->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelRequestEvent::class));
        $eventsMock->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelFinishRequestEvent::class));

        return $eventsMock;
    }

    /**
     * @return \Mockery\MockInterface
     */
    private function arrangeServerRequestWithXPhpObLevel(): MockInterface
    {
        $serverRequestMock = Mock::mock(ServerRequestInterface::class);
        $serverRequestMock->shouldReceive('withAddedHeader')
            ->once()
            ->with('X-Php-Ob-Level', (string) \ob_get_level())
            ->andReturn($serverRequestMock);

        return $serverRequestMock;
    }

    /**
     * @param \Mockery\MockInterface|\Psr\Container\ContainerInterface $container
     */
    private function arrangeDispatcher($container): void
    {
        $dispatcherMock = Mock::mock(DispatcherContract::class);
        $dispatcherMock->shouldReceive('setCachePath')
            ->once()
            ->with($this->routeCachePath);
        $dispatcherMock->shouldReceive('refreshCache')
            ->once()
            ->with(true);

        $container->shouldReceive('get')
            ->once()
            ->with(DispatcherContract::class)
            ->andReturn($dispatcherMock);
    }

    /**
     * @param Exception                                                       $exception
     * @param \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \Mockery\MockInterface|\Psr\Container\ContainerInterface        $container
     */
    private function arrangeExceptionHandler(Exception $exception, $serverRequest, $container): void
    {
        $handlerMock = Mock::mock(HttpHandlerContract::class);
        $handlerMock->shouldReceive('report')
            ->once()
            ->with($exception);
        $handlerMock->shouldReceive('render')
            ->once()
            ->with($serverRequest, $exception);

        $container->shouldReceive('has')
            ->twice()
            ->with(HttpHandlerContract::class)
            ->andReturnTrue();

        $container->shouldReceive('get')
            ->twice()
            ->with(HttpHandlerContract::class)
            ->andReturn($handlerMock);
    }
}
