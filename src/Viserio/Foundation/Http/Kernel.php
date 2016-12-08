<?php
declare(strict_types=1);
namespace Viserio\Foundation\Http;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Exception\Handler as HandlerContract;
use Viserio\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Contracts\Foundation\Terminable as TerminableContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Foundation\Bootstrap\DetectEnvironment;
use Viserio\Foundation\Bootstrap\HandleExceptions;
use Viserio\Foundation\Bootstrap\LoadConfiguration;
use Viserio\Foundation\Bootstrap\LoadRoutes;
use Viserio\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Routing\Pipeline;
use Viserio\Routing\Router;
use Viserio\Session\Middleware\StartSessionMiddleware;
use Viserio\StaticalProxy\StaticalProxy;
use Viserio\View\Middleware\ShareErrorsFromSessionMiddleware;

class Kernel implements TerminableContract, KernelContract, ServerMiddlewareInterface
{
    use EventsAwareTrait;

    /**
     * The application implementation.
     *
     * @var \Viserio\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The router instance.
     *
     * @var \Viserio\Contracts\Routing\Router
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
        DetectEnvironment::class,
        HandleExceptions::class,
        LoadRoutes::class,
        LoadServiceProvider::class,
    ];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     * @param \Viserio\Contracts\Routing\Router         $router
     * @param \Viserio\Contracts\Events\Dispatcher      $events
     */
    public function __construct(
        ApplicationContract $app,
        RouterContract $router,
        DispatcherContract $events
    ) {
        $this->app = $app;
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
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $serverRequest = $serverRequest->withAddedHeader('X-Php-Ob-Level', ob_get_level());

        // Passes the request to the container
        $this->app->instance(ServerRequestInterface::class, $serverRequest);

        StaticalProxy::clearResolvedInstance('request');

        $this->events->trigger(self::REQUEST, [$serverRequest]);

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
        $this->events->trigger(self::TERMINATE, [$serverRequest, $response]);

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
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->sendRequestThroughRouter($request);

            $this->events->trigger(self::RESPONSE, [$request, $response]);
        } catch (Throwable $exception) {
            $this->reportException($exception);

            $response = $this->renderException($request, $exception);

            $this->events->trigger(self::EXCEPTION, [$request, $response]);
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
                return $router->dispatch($request);
            });
    }
}
