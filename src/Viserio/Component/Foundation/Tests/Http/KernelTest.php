<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Http;

use Exception;
use Mockery as Mock;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contract\Routing\Router as  RouterContract;
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
            public $middlewares = [];

            /**
             * @var array
             */
            protected $middlewareGroups = [
                'test' => ['web'],
            ];
        };

        $kernel->prependMiddleware('test_1');
        $kernel->prependMiddleware('test_2');

        self::assertSame(['test_2', 'test_1'], $kernel->middlewares);
    }

    public function testPushMiddleware(): void
    {
        $kernel = new class() extends Kernel {
            /**
             * The application's middleware stack.
             *
             * @var array
             */
            public $middlewares = [];
        };

        $kernel->prependMiddleware('test_1');
        $kernel->pushMiddleware('test_3');
        $kernel->prependMiddleware('test_2');

        self::assertSame(['test_2', 'test_1', 'test_3'], $kernel->middlewares);
    }

    public function testHandle(): void
    {
        $serverRequest = $this->arrangeServerRequestWithXPhpObLevel();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $container->shouldReceive('get')
            ->once()
            ->with(EventManagerContract::class)
            ->andReturn($this->arrangeKernelEvents());

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $this->arrangeBootstrapManager($container);

        $container->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $this->arrangeLoadServiceProvider($container);

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

        $this->arrangeBootstrapManager($container);

        $events = $this->arrangeKernelEvents();
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelExceptionEvent::class));

        $container->shouldReceive('get')
            ->once()
            ->with(EventManagerContract::class)
            ->andReturn($events);

        $this->arrangeLoadServiceProvider($container);

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

        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $container->shouldReceive('get')
            ->times(3)
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);

        $container->shouldReceive('get')
            ->once()
            ->with(DispatcherContract::class)
            ->andReturn($this->mock(DispatcherContract::class));

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelTerminateEvent::class));

        $container->shouldReceive('get')
            ->once()
            ->with(EventManagerContract::class)
            ->andReturn($events);

        $this->arrangeLoadServiceProvider($container);

        $kernel = $this->getKernel($container);
        //Without bootstrap
        $kernel->terminate($serverRequest, $response);

        $kernel->bootstrap();

        $kernel->terminate($serverRequest, $response);
    }

    /**
     * @param \Mockery\MockInterface $container
     *
     * @return \Viserio\Component\Foundation\Http\Kernel
     */
    private function getKernel(MockInterface $container)
    {
        $kernel                      = new class($container) extends Kernel {
            protected $bootstrappers = [
                LoadServiceProvider::class,
            ];

            public function __construct($container)
            {
                parent::__construct();
                $this->container = $container;
            }

            /**
             * {@inheritdoc}
             */
            protected function initializeContainer(): void
            {
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
                    'env' => 'dev',
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
     * @return Mock\MockInterface
     */
    private function arrangeKernelEvents(): MockInterface
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
     * @return MockInterface
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
     * @param \Viserio\Component\Contract\Container\Container $container
     */
    private function arrangeDispatcher(ContainerContract $container): void
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
     * @param \Viserio\Component\Contract\Container\Container $container
     */
    private function arrangeLoadServiceProvider($container): void
    {
        $loader = $this->mock(LoadServiceProvider::class);
        $loader->shouldReceive('bootstrap')
            ->once();
        $container->shouldReceive('resolve')
            ->once()
            ->with(LoadServiceProvider::class)
            ->andReturn($loader);
    }

    /**
     * @param $exception
     * @param $serverRequest
     * @param $container
     */
    private function arrangeExceptionHandler($exception, $serverRequest, $container): void
    {
        $handler = $this->mock(ExceptionHandlerContract::class);
        $handler->shouldReceive('report')
            ->once()
            ->with($exception);
        $handler->shouldReceive('render')
            ->once()
            ->with($serverRequest, $exception);

        $container->shouldReceive('get')
            ->twice()
            ->with(ExceptionHandlerContract::class)
            ->andReturn($handler);
    }

    /**
     * @param $container
     */
    private function arrangeBootstrapManager($container): void
    {
        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $container->shouldReceive('get')
            ->once()
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);
    }
}
