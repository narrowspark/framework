<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Http;

use Exception;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Contracts\Profiler\Profiler as Profilertract;
use Viserio\Component\Contracts\Routing\Router as  RouterContract;
use Viserio\Component\Foundation\Bootstrap\HandleExceptions;
use Viserio\Component\Foundation\Bootstrap\LoadConfiguration;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Http\Kernel;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\View\Middleware\ShareErrorsFromSessionMiddleware;

class KernelTest extends MockeryTestCase
{
    public function testPrependMiddleware()
    {
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('setMiddlewarePriorities')
            ->once()
            ->with([
                StartSessionMiddleware::class,
                ShareErrorsFromSessionMiddleware::class,
            ]);
        $router->shouldReceive('addMiddlewares')
            ->once()
            ->with([]);
        $router->shouldReceive('withoutMiddleware')
            ->once()
            ->with('test');
        $router->shouldReceive('setMiddlewareGroup')
            ->once()
            ->with('test', ['web']);

        $kernel = new class($this->mock(ApplicationContract::class), $router, $this->mock(EventManagerContract::class)) extends Kernel {
            /**
             * The application's middleware stack.
             *
             * @var array
             */
            public $middlewares = [];

            /**
             * The application's route without a middleware.
             *
             * @var array
             */
            protected $routeWithoutMiddlewares = [
                'test',
            ];

            /**
             * The application's route middleware groups.
             *
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

    public function testPushMiddleware()
    {
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('setMiddlewarePriorities')
            ->once();
        $router->shouldReceive('addMiddlewares')
            ->once();

        $kernel = new class($this->mock(ApplicationContract::class), $router, $this->mock(EventManagerContract::class)) extends Kernel {
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

        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('setMiddlewarePriorities')
            ->once();
        $router->shouldReceive('addMiddlewares')
            ->once();
        $router->shouldReceive('setCachePath')
            ->once()
            ->with('');
        $router->shouldReceive('refreshCache')
            ->once()
            ->with(true);
        $router->shouldReceive('dispatch')
            ->once()
            ->with($serverRequest)
            ->andReturn($response);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->twice();

        $profiler = $this->mock(Profilertract::class);
        $profiler->shouldReceive('modifyResponse')
            ->once()
            ->with($serverRequest, $response)
            ->andReturn($response);

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('routing.path')
            ->andReturn('');
        $config->shouldReceive('get')
            ->once()
            ->with('app.env', 'production')
            ->andReturn(true);
        $config->shouldReceive('get')
            ->once()
            ->with('app.skip_middlewares', false)
            ->andReturn(false);

        $app = $this->mock(ApplicationContract::class);
        $app->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);
        $app->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(false);
        $app->shouldReceive('bootstrapWith')
            ->once()
            ->with([
                LoadConfiguration::class,
                LoadEnvironmentVariables::class,
                HandleExceptions::class,
                LoadServiceProvider::class,
            ]);
        $app->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);
        $app->shouldReceive('has')
            ->once()
            ->with(Profilertract::class)
            ->andReturn(true);
        $app->shouldReceive('get')
            ->once()
            ->with(Profilertract::class)
            ->andReturn($profiler);
        $app->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $kernel = new Kernel(
            $app,
            $router,
            $events
        );

        self::assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testHandleWithException()
    {
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
            ->with('');
        $router->shouldReceive('refreshCache')
            ->once()
            ->with(true);
        $router->shouldReceive('dispatch')
            ->once()
            ->with($serverRequest)
            ->andThrow($exception);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->twice();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('routing.path')
            ->andReturn('');
        $config->shouldReceive('get')
            ->once()
            ->with('app.env', 'production')
            ->andReturn(true);
        $config->shouldReceive('get')
            ->once()
            ->with('app.skip_middlewares', false)
            ->andReturn(false);
        $handler = $this->mock(HandlerContract::class);
        $handler->shouldReceive('report')
            ->once()
            ->with($exception);
        $handler->shouldReceive('render')
            ->once()
            ->with($serverRequest, $exception);

        $app = $this->mock(ApplicationContract::class);
        $app->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);
        $app->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(false);
        $app->shouldReceive('bootstrapWith')
            ->once()
            ->with([
                LoadConfiguration::class,
                LoadEnvironmentVariables::class,
                HandleExceptions::class,
                LoadServiceProvider::class,
            ]);
        $app->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);
        $app->shouldReceive('get')
            ->twice()
            ->with(HandlerContract::class)
            ->andReturn($handler);
        $app->shouldReceive('instance')
            ->once()
            ->with(ServerRequestInterface::class, $serverRequest);

        $kernel = new Kernel(
            $app,
            $router,
            $events
        );

        self::assertInstanceOf(ResponseInterface::class, $kernel->handle($serverRequest));
    }

    public function testTerminate()
    {
        $response      = $this->mock(ResponseInterface::class);
        $serverRequest = $this->mock(ServerRequestInterface::class);

        $handler = $this->mock(HandlerContract::class);
        $handler->shouldReceive('unregister')
            ->once();

        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('setMiddlewarePriorities')
            ->once()
            ->with([
                StartSessionMiddleware::class,
                ShareErrorsFromSessionMiddleware::class,
            ]);
        $router->shouldReceive('addMiddlewares')
            ->once()
            ->with([]);

        $app = $this->mock(ApplicationContract::class);
        $app->shouldReceive('get')
            ->once()
            ->with(HandlerContract::class)
            ->andReturn($handler);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once();

        $kernel = new Kernel(
            $app,
            $router,
            $events
        );

        $kernel->terminate($serverRequest, $response);
    }
}
