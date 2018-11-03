<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Tests;

use Exception;
use Mockery as Mock;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contract\Routing\Router as  RouterContract;
use Viserio\Component\Exception\Bootstrap\HttpHandleExceptions;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\BootstrapManager;
use Viserio\Component\HttpFoundation\Event\KernelExceptionEvent;
use Viserio\Component\HttpFoundation\Event\KernelFinishRequestEvent;
use Viserio\Component\HttpFoundation\Event\KernelRequestEvent;
use Viserio\Component\HttpFoundation\Event\KernelTerminateEvent;
use Viserio\Component\HttpFoundation\Kernel;

/**
 * @internal
 */
final class KernelTest extends MockeryTestCase
{
    /**
     * @var string
     */
    private $routeCachePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $fixturePath = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture';

        $this->routeCachePath = $fixturePath . \DIRECTORY_SEPARATOR . 'storage' . \DIRECTORY_SEPARATOR . 'framework' . \DIRECTORY_SEPARATOR . 'routes.cache.php';
    }

    public function testHttpHandle(): void
    {
        $serverRequest = $this->arrangeServerRequestWithXPhpObLevel();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $events = $this->arrangeKernelHandleEvents();

        $this->arrangeContainerEventsCalls($container, $events);

        $container->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('dispatch')
            ->once()
            ->with(Mock::type(ServerRequestInterface::class))
            ->andReturn($this->mock(ResponseInterface::class));

        $this->arrangeDispatcher($container);

        $container->shouldReceive('get')
            ->once()
            ->with(RouterContract::class)
            ->andReturn($router);

        $kernel = $this->getKernel($container);

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($this->arrangeBootstrapManager($kernel));

        $this->assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testHandleWithException(): void
    {
        $container = $this->mock(ContainerContract::class);
        $exception = new Exception('test');

        $serverRequest = $this->arrangeServerRequestWithXPhpObLevel();

        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('dispatch')
            ->once()
            ->with($serverRequest)
            ->andThrow($exception);

        $container->shouldReceive('get')
            ->once()
            ->with(RouterContract::class)
            ->andReturn($router);

        $this->arrangeExceptionHandler($exception, $serverRequest, $container);
        $this->arrangeDispatcher($container);

        $events = $this->arrangeKernelHandleEvents();
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelExceptionEvent::class));

        $this->arrangeContainerEventsCalls($container, $events);

        $container->shouldReceive('instance')
            ->twice()
            ->with(ServerRequestInterface::class, $serverRequest);

        $kernel = $this->getKernel($container);

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($this->arrangeBootstrapManager($kernel));

        $this->assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testTerminate(): void
    {
        $responseMock      = $this->mock(ResponseInterface::class);
        $serverRequestMock = $this->mock(ServerRequestInterface::class);
        $containerMock     = $this->mock(ContainerContract::class);

        $containerMock->shouldReceive('get')
            ->once()
            ->with(DispatcherContract::class)
            ->andReturn($this->mock(DispatcherContract::class));

        $eventsMock = $this->mock(EventManagerContract::class);
        $eventsMock->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelTerminateEvent::class));

        $this->arrangeContainerEventsCalls($containerMock, $eventsMock);

        $kernel = $this->getKernel($containerMock);

        $bootstrapManager = $this->mock(new BootstrapManager($kernel));

        $containerMock->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);

        $bootstrapManager->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(false);

        //Without bootstrap
        $kernel->terminate($serverRequestMock, $responseMock);

        $bootstrapManager->shouldReceive('bootstrapWith')
            ->with([
                ConfigureKernel::class,
            ]);

        $bootstrapManager->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(false);

        $kernel->bootstrap();

        $bootstrapManager->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(true);

        $kernel->terminate($serverRequestMock, $responseMock);
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Events\EventManager $events
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
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     *
     * @return \Viserio\Component\HttpFoundation\Kernel
     */
    private function getKernel(MockInterface $container): Kernel
    {
        $kernel                      = new class($container) extends Kernel {
            private $testContainer;

            public function __construct($container)
            {
                $this->testContainer = $container;

                parent::__construct();
            }

            /**
             * {@inheritdoc}
             */
            public function getRootDir(): string
            {
                return $this->rootDir = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture';
            }

            /**
             * {@inheritdoc}
             */
            protected function initializeContainer(): ContainerContract
            {
                return $this->testContainer;
            }

            /**
             * {@inheritdoc}
             */
            protected function registerBaseBindings(): void
            {
            }
        };

        $kernel->setKernelConfigurations([
            'viserio' => [
                'app' => [
                    'env'   => 'dev',
                    'debug' => true,
                ],
            ],
        ]);

        return $kernel;
    }

    /**
     * @return \Mockery\MockInterface
     */
    private function arrangeKernelHandleEvents(): MockInterface
    {
        $eventsMock = $this->mock(EventManagerContract::class);
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
        $serverRequestMock = $this->mock(ServerRequestInterface::class);
        $serverRequestMock->shouldReceive('withAddedHeader')
            ->once()
            ->with('X-Php-Ob-Level', (string) \ob_get_level())
            ->andReturn($serverRequestMock);

        return $serverRequestMock;
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     */
    private function arrangeDispatcher($container): void
    {
        $dispatcherMock = $this->mock(DispatcherContract::class);
        $dispatcherMock->shouldReceive('setCachePath')
            ->once()
            ->with($this->routeCachePath);
        $dispatcherMock->shouldReceive('refreshCache')
            ->once()
            ->with(true);

        $container->shouldReceive('get')
            ->twice()
            ->with(DispatcherContract::class)
            ->andReturn($dispatcherMock);
    }

    /**
     * @param \Exception                                                             $exception
     * @param \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface        $serverRequest
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     */
    private function arrangeExceptionHandler(Exception $exception, $serverRequest, $container): void
    {
        $handlerMock = $this->mock(HttpHandlerContract::class);
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

    /**
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     *
     * @return \Mockery\MockInterface|\Viserio\Component\Foundation\BootstrapManager
     */
    private function arrangeBootstrapManager($kernel)
    {
        $bootstrapManagerMock = $this->mock(new BootstrapManager($kernel));
        $bootstrapManagerMock->shouldReceive('addAfterBootstrapping')
            ->once()
            ->with(LoadServiceProvider::class, [HttpHandleExceptions::class, 'bootstrap']);
        $bootstrapManagerMock->shouldReceive('bootstrapWith')
            ->with([
                ConfigureKernel::class,
            ]);

        return $bootstrapManagerMock;
    }
}
