<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Http;

use Exception;
use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contracts\Routing\Router as  RouterContract;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Events\BootstrappedEvent;
use Viserio\Component\Foundation\Events\BootstrappingEvent;
use Viserio\Component\Foundation\Http\Events\KernelExceptionEvent;
use Viserio\Component\Foundation\Http\Events\KernelFinishRequestEvent;
use Viserio\Component\Foundation\Http\Events\KernelRequestEvent;
use Viserio\Component\Foundation\Http\Events\KernelResponseEvent;
use Viserio\Component\Foundation\Http\Events\KernelTerminateEvent;
use Viserio\Component\Foundation\Http\Kernel;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;

class KernelTest extends MockeryTestCase
{
    public function testPrependMiddleware()
    {
        $kernel                 = new class() extends Kernel {
            public $middlewares = [];

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

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelRequestEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelFinishRequestEvent::class));
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
        $dispatcher = $this->mock(DispatcherContract::class);
        $dispatcher->shouldReceive('setCachePath')
            ->once()
            ->with('/storage/framework/routes.cache.php');
        $dispatcher->shouldReceive('refreshCache')
            ->once()
            ->with(true);

        $container->shouldReceive('get')
            ->once()
            ->with(RouterContract::class)
            ->andReturn($router);
        $container->shouldReceive('get')
            ->once()
            ->with(DispatcherContract::class)
            ->andReturn($dispatcher);

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

        $dispatcher = $this->mock(DispatcherContract::class);
        $dispatcher->shouldReceive('setCachePath')
            ->once()
            ->with('/storage/framework/routes.cache.php');
        $dispatcher->shouldReceive('refreshCache')
            ->once()
            ->with(true);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('dispatch')
            ->once()
            ->with($serverRequest)
            ->andThrow($exception);

        $container->shouldReceive('get')
            ->once()
            ->with(RouterContract::class)
            ->andReturn($router);

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
        $container->shouldReceive('get')
            ->once()
            ->with(DispatcherContract::class)
            ->andReturn($dispatcher);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelRequestEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(KernelFinishRequestEvent::class));
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

        $kernel->bootstrap();

        $kernel->terminate($serverRequest, $response);
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
