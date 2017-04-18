<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Http;

use Closure;
use Exception;
use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contracts\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Contracts\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\Routing\Router as  RouterContract;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\EnvironmentDetector;
use Viserio\Component\Foundation\Events\BootstrappedEvent;
use Viserio\Component\Foundation\Events\BootstrappingEvent;
use Viserio\Component\Foundation\Http\Events\KernelExceptionEvent;
use Viserio\Component\Foundation\Http\Events\KernelRequestEvent;
use Viserio\Component\Foundation\Http\Events\KernelResponseEvent;
use Viserio\Component\Foundation\Http\Events\KernelTerminateEvent;
use Viserio\Component\Foundation\Http\Kernel;
use Viserio\Component\Foundation\Providers\ConfigureLoggingServiceProvider;
use Viserio\Component\Log\Providers\LoggerServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Parsers\Providers\ParsersServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;

class KernelTest extends MockeryTestCase
{
    public function testPrependMiddleware()
    {
        $kernel                 = new class() extends Kernel {
            public $middlewares = [];

            protected $routeWithoutMiddlewares = [
                'test',
            ];

            protected $middlewareGroups = [
                'test' => ['web'],
            ];
        };

        $kernel->prependMiddleware('test_1');
        $kernel->prependMiddleware('test_2');

        self::assertSame(['test_2', 'test_1'], $kernel->middlewares);
    }

    public function testPushMiddleware()
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

    public function testHandle()
    {
        $response = $this->mock(ResponseInterface::class);

        $serverRequest = $this->mock(ServerRequestInterface::class);
        $serverRequest->shouldReceive('withAddedHeader')
            ->once()
            ->with('X-Php-Ob-Level', (string) ob_get_level())
            ->andReturn($serverRequest);

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.env', 'production')
            ->andReturn(true);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.skip_middlewares', false)
            ->andReturn(false);

        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelRequestEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelResponseEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappedEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappingEvent::class));

        $container->shouldReceive('get')
            ->twice()
            ->with(EventManagerContract::class)
            ->andReturn($events);

        $container->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $loader = $this->mock(LoadServiceProvider::class);
        $loader->shouldReceive('bootstrap')
            ->once();
        $container->shouldReceive('resolve')
            ->once()
            ->with(LoadServiceProvider::class)
            ->andReturn($loader);

        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('dispatch')
            ->once()
            ->with(Mock::type(ServerRequestInterface::class))
            ->andReturn($this->mock(ResponseInterface::class));
        $router->shouldReceive('setMiddlewarePriorities')
            ->once();
        $router->shouldReceive('addMiddlewares')
            ->once();
        $router->shouldReceive('setCachePath')
            ->once()
            ->with('/storage/routes');
        $router->shouldReceive('refreshCache')
            ->once()
            ->with(true);

        $container->shouldReceive('get')
            ->twice()
            ->with(RouterContract::class)
            ->andReturn($router);

        $this->registerBaseProvider($container);
        $this->getBootstrap($container);

        $kernel = $this->getKernel($container);

        self::assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testHandleWithException()
    {
        $container = $this->mock(ContainerContract::class);

        $exception = new Exception();
        $response  = $this->mock(ResponseInterface::class);

        $serverRequest = $this->mock(ServerRequestInterface::class);
        $serverRequest->shouldReceive('withAddedHeader')
            ->once()
            ->with('X-Php-Ob-Level', (string) ob_get_level())
            ->andReturn($serverRequest);

        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('setMiddlewarePriorities')
            ->once();
        $router->shouldReceive('addMiddlewares')
            ->once();
        $router->shouldReceive('setCachePath')
            ->once()
            ->with('/storage/routes');
        $router->shouldReceive('refreshCache')
            ->once()
            ->with(true);
        $router->shouldReceive('dispatch')
            ->once()
            ->with($serverRequest)
            ->andThrow($exception);

        $container->shouldReceive('get')
            ->twice()
            ->with(RouterContract::class)
            ->andReturn($router);

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.env', 'production')
            ->andReturn(true);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.skip_middlewares', false)
            ->andReturn(false);

        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);

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

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelRequestEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelExceptionEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappedEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappingEvent::class));

        $container->shouldReceive('get')
            ->twice()
            ->with(EventManagerContract::class)
            ->andReturn($events);

        $loader = $this->mock(LoadServiceProvider::class);
        $loader->shouldReceive('bootstrap')
            ->once();
        $container->shouldReceive('resolve')
            ->once()
            ->with(LoadServiceProvider::class)
            ->andReturn($loader);

        $container->shouldReceive('instance')
            ->twice()
            ->with(ServerRequestInterface::class, $serverRequest);

        $this->getBootstrap($container);
        $this->registerBaseProvider($container);

        $kernel = $this->getKernel($container);

        self::assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testTerminate()
    {
        $response      = $this->mock(ResponseInterface::class);
        $serverRequest = $this->mock(ServerRequestInterface::class);

        $container = $this->mock(ContainerContract::class);

        $kernel = $this->getKernel($container);

        $kernel->terminate($serverRequest, $response);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelTerminateEvent::class));

        $container->shouldReceive('get')
            ->once()
            ->with(EventManagerContract::class)
            ->andReturn($events);

        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('setMiddlewarePriorities')
            ->once();
        $router->shouldReceive('addMiddlewares')
            ->once();

        $container->shouldReceive('get')
            ->once()
            ->with(RouterContract::class)
            ->andReturn($router);

        $this->getBootstrap($container);
        $this->registerBaseProvider($container);

        $kernel->terminate($serverRequest, $response);
        $kernel->boot();

        $kernel->terminate($serverRequest, $response);
    }

    private function getBootstrap($container)
    {
        $container->shouldReceive('singleton')
            ->once()
            ->with(EnvironmentContract::class, EnvironmentDetector::class);
        $container->shouldReceive('singleton')
            ->once()
            ->with(KernelContract::class, Mock::type(Closure::class));
        $container->shouldReceive('alias')
            ->once()
            ->with(KernelContract::class, 'kernel');
        $container->shouldReceive('alias')
            ->once()
            ->with(EnvironmentDetector::class, EnvironmentContract::class);
        $container->shouldReceive('singleton')
            ->once()
            ->with(HttpKernelContract::class, Mock::type(Closure::class));
        $container->shouldReceive('alias')
            ->once()
            ->with(KernelContract::class, AbstractKernel::class);
        $container->shouldReceive('alias')
            ->once()
            ->with(HttpKernelContract::class, 'http_kernel');
        $container->shouldReceive('alias')
            ->once()
            ->with(HttpKernelContract::class, Kernel::class);
    }

    private function registerBaseProvider($container)
    {
        $container->shouldReceive('register')
            ->once()
            ->with(Mock::type(EventsServiceProvider::class));
        $container->shouldReceive('register')
            ->once()
            ->with(Mock::type(OptionsResolverServiceProvider::class));
        $container->shouldReceive('register')
            ->once()
            ->with(Mock::type(ConfigServiceProvider::class));
        $container->shouldReceive('register')
            ->once()
            ->with(Mock::type(RoutingServiceProvider::class));
    }

    private function getKernel($container)
    {
        return new class($container) extends Kernel {
            protected $bootstrappers = [
                LoadServiceProvider::class,
            ];

            public function __construct($container)
            {
                $this->container = $container;
            }

            protected function initializeContainer(): void
            {
            }
        };
    }
}
