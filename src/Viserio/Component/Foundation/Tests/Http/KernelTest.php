<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Http;

use Closure;
use Exception;
use Mockery as Mock;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contract\Routing\Router as  RouterContract;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\BootstrapManager;
use Viserio\Component\Foundation\Http\Event\KernelExceptionEvent;
use Viserio\Component\Foundation\Http\Event\KernelFinishRequestEvent;
use Viserio\Component\Foundation\Http\Event\KernelRequestEvent;
use Viserio\Component\Foundation\Http\Event\KernelTerminateEvent;
use Viserio\Component\Foundation\Http\Kernel;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class KernelTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var string
     */
    private $routeCachePath;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->routeCachePath = self::normalizeDirectorySeparator(\dirname(__DIR__, 2) . '/storage/framework/routes.cache.php');
    }

    public function testPrependMiddleware(): void
    {
        $kernel = new class() extends Kernel {
            /**
             * @var array
             */
            public $middleware = [];

            /**
             * @var array
             */
            protected $middlewareGroups = [
                'test' => ['web'],
            ];
        };

        $kernel->prependMiddleware('test_1');
        $kernel->prependMiddleware('test_2');

        self::assertSame(['test_2', 'test_1'], $kernel->middleware);
    }

    public function testPushMiddleware(): void
    {
        $kernel = new class() extends Kernel {
            /**
             * The application's middleware stack.
             *
             * @var array
             */
            public $middleware = [];
        };

        $kernel->prependMiddleware('test_1');
        $kernel->pushMiddleware('test_3');
        $kernel->prependMiddleware('test_2');

        self::assertSame(['test_2', 'test_1', 'test_3'], $kernel->middleware);
    }

    public function testHandle(): void
    {
        $serverRequest = $this->arrangeServerRequestWithXPhpObLevel();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $events = $this->arrangeKernelHandleEvents();

        $this->arrangeContainerEventsCalls($container, $events);

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $container->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $this->arrangeBootstrapManager($container);

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

        self::assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testHandleWithException(): void
    {
        $container = $this->mock(ContainerContract::class);
        $exception = new Exception();

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

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $events = $this->arrangeKernelHandleEvents();
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelExceptionEvent::class));

        $this->arrangeContainerEventsCalls($container, $events);

        $this->arrangeBootstrapManager($container);

        $container->shouldReceive('instance')
            ->twice()
            ->with(ServerRequestInterface::class, $serverRequest);

        $kernel = $this->getKernel($container);

        self::assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testTerminate(): void
    {
        $response      = $this->mock(ResponseInterface::class);
        $serverRequest = $this->mock(ServerRequestInterface::class);
        $container     = $this->mock(ContainerContract::class);

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $container->shouldReceive('get')
            ->once()
            ->with(DispatcherContract::class)
            ->andReturn($this->mock(DispatcherContract::class));

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelTerminateEvent::class));

        $this->arrangeContainerEventsCalls($container, $events);

        $this->arrangeBootstrapManager($container);

        $kernel = $this->getKernel($container);
        //Without bootstrap
        $kernel->terminate($serverRequest, $response);

        $kernel->bootstrap();

        $kernel->terminate($serverRequest, $response);
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
     * @return \Viserio\Component\Foundation\Http\Kernel
     */
    private function getKernel(MockInterface $container)
    {
        $kernel                      = new class($container) extends Kernel {
            private $testContainer;

            protected $bootstrappers = [
                LoadServiceProvider::class,
            ];

            public function __construct($container)
            {
                $this->testContainer = $container;

                parent::__construct();
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
            protected function registerBaseServiceProviders(): void
            {
            }

            /**
             * {@inheritdoc}
             */
            protected function registerBaseBindings(): void
            {
            }
        };

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'app' => [
                    'env'   => 'dev',
                    'debug' => true,
                ],
            ]);
        $container->shouldReceive('has')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $kernel->setKernelConfigurations($container);

        return $kernel;
    }

    /**
     * @return \Mockery\MockInterface
     */
    private function arrangeKernelHandleEvents(): MockInterface
    {
        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelRequestEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelFinishRequestEvent::class));

        return $events;
    }

    /**
     * @return \Mockery\MockInterface
     */
    private function arrangeServerRequestWithXPhpObLevel(): MockInterface
    {
        $serverRequest = $this->mock(ServerRequestInterface::class);
        $serverRequest->shouldReceive('withAddedHeader')
            ->once()
            ->with('X-Php-Ob-Level', (string) \ob_get_level())
            ->andReturn($serverRequest);

        return $serverRequest;
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     */
    private function arrangeDispatcher($container): void
    {
        $dispatcher = $this->mock(DispatcherContract::class);
        $dispatcher->shouldReceive('setCachePath')
            ->once()
            ->with($this->routeCachePath);
        $dispatcher->shouldReceive('refreshCache')
            ->once()
            ->with(true);

        $container->shouldReceive('get')
            ->twice()
            ->with(DispatcherContract::class)
            ->andReturn($dispatcher);
    }

    /**
     * @param \Exception                                                             $exception
     * @param \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface        $serverRequest
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     */
    private function arrangeExceptionHandler(\Exception $exception, $serverRequest, $container): void
    {
        $handler = $this->mock(HttpHandlerContract::class);
        $handler->shouldReceive('report')
            ->once()
            ->with($exception);
        $handler->shouldReceive('render')
            ->once()
            ->with($serverRequest, $exception);

        $container->shouldReceive('get')
            ->twice()
            ->with(HttpHandlerContract::class)
            ->andReturn($handler);
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     */
    private function arrangeBootstrapManager($container): void
    {
        $loader = $this->mock(LoadServiceProvider::class);
        $loader->shouldReceive('bootstrap')
            ->once();

        $container->shouldReceive('resolve')
            ->once()
            ->with(LoadServiceProvider::class)
            ->andReturn($loader);

        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $bootstrapManager->shouldReceive('addBeforeBootstrapping')
            ->twice()
            ->with(ConfigureKernel::class, \Mockery::type(Closure::class));
        $bootstrapManager->shouldReceive('addAfterBootstrapping')
            ->once()
            ->with(LoadServiceProvider::class, \Mockery::type(Closure::class));

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);
    }
}
