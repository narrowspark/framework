<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
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

class Kernel implements TerminableContract, KernelContract
{
    use EventsAwareTrait;

    /**
     * The application implementation.
     *
     * @var \Viserio\Component\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The router instance.
     *
     * @var \Viserio\Component\Contracts\Routing\Router
     */
    protected $router;

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
     * Create a new HTTP kernel instance.
     *
     * @param \Viserio\Component\Contracts\Foundation\Application $app
     * @param \Viserio\Component\Contracts\Routing\Router         $router
     * @param \Viserio\Component\Contracts\Events\EventManager    $events
     */
    public function __construct(
        ApplicationContract $app,
        RouterContract $router,
        EventManagerContract $events
    ) {
        $this->app    = $app;
        $this->events = $events;

        $router->setMiddlewarePriorities($this->middlewarePriority);
        $router->addMiddlewares($this->routeMiddlewares);

        foreach ($this->routeWithoutMiddlewares as $routeWithoutMiddleware) {
            $router->withoutMiddleware($routeWithoutMiddleware);
        }

        foreach ($this->middlewareGroups as $key => $middleware) {
            $router->setMiddlewareGroup($key, $middleware);
        }

        $this->router = $router;
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
        $serverRequest = $serverRequest->withAddedHeader('X-Php-Ob-Level', (string) ob_get_level());

        // Passes the request to the container
        $this->app->instance(ServerRequestInterface::class, $serverRequest);

        StaticalProxy::clearResolvedInstance('request');

        $this->events->trigger(new KernelRequestEvent($this, $serverRequest));

        $this->bootstrap();

        $response = $this->handleRequest($serverRequest);

        // stop PHP sending a Content-Type automatically
        ini_set('default_mimetype', '');

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $this->events->trigger(new KernelTerminateEvent($this, $serverRequest, $response));

        $this->app->get(HandlerContract::class)->unregister();
    }

    /**
     * Bootstrap the application for HTTP requests.
     */
    public function bootstrap()
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers);
        }
    }

    /**
     * Convert request into response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleRequest(ServerRequestInterface $serverRequest): ResponseInterface
    {
        try {
            $response = $this->sendRequestThroughRouter($serverRequest);

            if ($this->app->has(WebProfilerContract::class)) {
                // Modify the response to add the webprofiler
                $response = $this->app->get(WebProfilerContract::class)->modifyResponse(
                    $serverRequest,
                    $response
                );
            }

            $this->events->trigger(new KernelResponseEvent($this, $serverRequest, $response));
        } catch (Throwable $exception) {
            $this->reportException($exception);

            $response = $this->renderException($serverRequest, $exception);

            $this->events->trigger(new KernelExceptionEvent($this, $serverRequest, $response));
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
        $this->app->get(HandlerContract::class)->report($exception);
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
        return $this->app->get(HandlerContract::class)->render($request, $exception);
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
        $router = $this->router;
        $config = $this->app->get(RepositoryContract::class);

        $router->setCachePath($config->get('routing.path'));
        $router->refreshCache($config->get('app.env', 'production') !== 'production');

        return (new Pipeline())
            ->setContainer($this->app)
            ->send($request)
            ->through($config->get('app.skip_middlewares', false) ? [] : $this->middlewares)
            ->then(function ($request) use ($router) {
                $this->app->instance(ServerRequestInterface::class, $request);

                return $router->dispatch($request);
            });
    }
}
