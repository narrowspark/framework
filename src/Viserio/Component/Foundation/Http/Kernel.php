<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Contracts\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\HandleExceptions;
use Viserio\Component\Foundation\Bootstrap\LoadConfiguration;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Http\Events\KernelExceptionEvent;
use Viserio\Component\Foundation\Http\Events\KernelRequestEvent;
use Viserio\Component\Foundation\Http\Events\KernelResponseEvent;
use Viserio\Component\Foundation\Http\Events\KernelTerminateEvent;
use Viserio\Component\Routing\Pipeline;
use Viserio\Component\Routing\Router;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Component\View\Middleware\ShareErrorsFromSessionMiddleware;

class Kernel extends AbstractKernel implements HttpKernelContract, TerminableContract
{
    /**
     * The application's middleware stack.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddlewares = [];

    /**
     * The application's route without a middleware.
     *
     * @var array
     */
    protected $routeWithoutMiddlewares = [];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        StartSessionMiddleware::class,
        ShareErrorsFromSessionMiddleware::class,
    ];

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        LoadConfiguration::class,
        LoadEnvironmentVariables::class,
        HandleExceptions::class,
        LoadServiceProvider::class,
    ];

    /**
     * Boots the current kernel.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        parent::boot();

        $router = $this->getContainer()->get(RouterContract::class);

        $router->setMiddlewarePriorities($this->middlewarePriority);
        $router->addMiddlewares($this->routeMiddlewares);

        foreach ($this->routeWithoutMiddlewares as $routeWithoutMiddleware) {
            $router->withoutMiddleware($routeWithoutMiddleware);
        }

        foreach ($this->middlewareGroups as $key => $middleware) {
            $router->setMiddlewareGroup($key, $middleware);
        }

        $this->booted = true;
    }

    /**
     * Add a new middleware to beginning of the stack if it does not already exist.
     *
     * @param string $middleware
     *
     * @return $this
     */
    public function prependMiddleware(string $middleware)
    {
        if (array_search($middleware, $this->middlewares) === false) {
            array_unshift($this->middlewares, $middleware);
        }

        return $this;
    }

    /**
     * Add a new middleware to end of the stack if it does not already exist.
     *
     * @param string $middleware
     *
     * @return $this
     */
    public function pushMiddleware(string $middleware)
    {
        if (array_search($middleware, $this->middlewares) === false) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $this->boot();

        $serverRequest = $serverRequest->withAddedHeader('X-Php-Ob-Level', (string) ob_get_level());
        $container     = $this->getContainer();

        // Passes the request to the container
        $container->instance(ServerRequestInterface::class, $serverRequest);

        StaticalProxy::clearResolvedInstance(ServerRequestInterface::class);
        $evetns = $container->get(EventManagerContract::class);

        $evetns->trigger(new KernelRequestEvent($this, $serverRequest));

        $this->bootstrap();

        $response = $this->handleRequest($serverRequest, $evetns);

        // stop PHP sending a Content-Type automatically
        ini_set('default_mimetype', '');

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        if ($this->booted === false) {
            return;
        }

        $container = $this->getContainer();

        $container->get(EventManagerContract::class)->trigger(new KernelTerminateEvent($this, $serverRequest, $response));

        $container->get(HandlerContract::class)->unregister();
    }

    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        if (! $this->hasBeenBootstrapped()) {
            $this->bootstrapWith($this->bootstrappers);
        }
    }

    /**
     * Convert request into response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface         $serverRequest
     * @param \Viserio\Component\Contracts\Events\EventManager $events
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleRequest(ServerRequestInterface $serverRequest, EventManagerContract $events): ResponseInterface
    {
        try {
            $response = $this->sendRequestThroughRouter($serverRequest);

            $events->trigger(new KernelResponseEvent($this, $serverRequest, $response));
        } catch (Throwable $exception) {
            $this->reportException($exception);

            $response = $this->renderException($serverRequest, $exception);

            $events->trigger(new KernelExceptionEvent($this, $serverRequest, $response));
        }

        return $response;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Throwable $exception
     */
    protected function reportException(Throwable $exception)
    {
        $this->getContainer()->get(HandlerContract::class)->report($exception);
    }

    /**
     * Render the exception to a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function renderException(
        ServerRequestInterface $request,
        Throwable $exception
    ): ResponseInterface {
        return $this->getContainer()->get(HandlerContract::class)->render($request, $exception);
    }

    /**
     * Send the given request through the middleware / router.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendRequestThroughRouter(ServerRequestInterface $request): ResponseInterface
    {
        $container = $this->getContainer();
        $router    = $container->get(RouterContract::class);
        $config    = $container->get(RepositoryContract::class);

        $router->setCachePath($this->getStoragePath('routes'));
        $router->refreshCache($config->get('viserio.app.env', 'production') !== 'production');

        return (new Pipeline())
            ->setContainer($container)
            ->send($request)
            ->through($config->get('viserio.app.skip_middlewares', false) ? [] : $this->middlewares)
            ->then(function ($request) use ($router, $container) {
                $container->instance(ServerRequestInterface::class, $request);

                return $router->dispatch($request);
            });
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings(): void
    {
        parent::registerBaseBindings();

        $kernel    = $this;
        $container = $this->getContainer();

        $container->singleton(HttpKernelContract::class, function () use ($kernel) {
            return $kernel;
        });

        $container->alias(HttpKernelContract::class, self::class);
        $container->alias(HttpKernelContract::class, 'http_kernel');
    }
}
