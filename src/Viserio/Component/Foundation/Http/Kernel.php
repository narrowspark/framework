<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Contracts\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contracts\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;
use Viserio\Component\Foundation\Bootstrap\HandleExceptions;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\BootstrapManager;
use Viserio\Component\Foundation\Http\Event\KernelExceptionEvent;
use Viserio\Component\Foundation\Http\Event\KernelFinishRequestEvent;
use Viserio\Component\Foundation\Http\Event\KernelRequestEvent;
use Viserio\Component\Foundation\Http\Event\KernelTerminateEvent;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Profiler\Middleware\ProfilerMiddleware;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Pipeline as RoutingPipeline;
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
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        StartSessionMiddleware::class,
        ShareErrorsFromSessionMiddleware::class,
        99999 => ProfilerMiddleware::class,
    ];

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        LoadEnvironmentVariables::class,
        ConfigureKernel::class,
        HandleExceptions::class,
    ];

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        $options = [
            'name'             => 'Narrowspark',
            'skip_middlewares' => false,
        ];

        return \array_merge(parent::getDefaultOptions(), $options);
    }

    /**
     * Add a new middleware to beginning of the stack if it does not already exist.
     *
     * @param string $middleware
     *
     * @return $this
     */
    public function prependMiddleware(string $middleware): self
    {
        if (\in_array($middleware, $this->middlewares, true) === false) {
            \array_unshift($this->middlewares, $middleware);
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
    public function pushMiddleware(string $middleware): self
    {
        if (\in_array($middleware, $this->middlewares, true) === false) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $serverRequest = $serverRequest->withAddedHeader('X-Php-Ob-Level', (string) \ob_get_level());

        $this->bootstrap();

        $container = $this->getContainer();
        $events    = $container->get(EventManagerContract::class);

        $events->trigger(new KernelRequestEvent($this, $serverRequest));

        // Passes the request to the container
        $container->instance(ServerRequestInterface::class, $serverRequest);

        if (\class_exists(StaticalProxy::class)) {
            StaticalProxy::clearResolvedInstance(ServerRequestInterface::class);
        }

        $response = $this->handleRequest($serverRequest, $events);

        // Stop PHP sending a Content-Type automatically.
        \ini_set('default_mimetype', '');

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        if (! $this->getContainer()->get(BootstrapManager::class)->hasBeenBootstrapped()) {
            return;
        }

        $this->getContainer()->get(EventManagerContract::class)->trigger(new KernelTerminateEvent($this, $serverRequest, $response));

        \restore_error_handler();
    }

    /**
     * Bootstrap the kernel for HTTP requests.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        $container        = $this->getContainer();
        $bootstrapManager = $container->get(BootstrapManager::class);

        if (! $bootstrapManager->hasBeenBootstrapped()) {
            $bootstrapManager->bootstrapWith($this->bootstrappers);

            $dispatcher = $container->get(DispatcherContract::class);

            if ($dispatcher instanceof MiddlewareBasedDispatcher) {
                $dispatcher->setMiddlewarePriorities($this->middlewarePriority);
                $dispatcher->withMiddleware($this->routeMiddlewares);

                foreach ($this->middlewareGroups as $key => $middleware) {
                    $dispatcher->setMiddlewareGroup($key, $middleware);
                }
            }
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
            $events->trigger(new KernelFinishRequestEvent($this, $serverRequest));

            $response = $this->sendRequestThroughRouter($serverRequest);
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
     *
     * @return void
     */
    protected function reportException(Throwable $exception): void
    {
        $this->getContainer()->get(ExceptionHandlerContract::class)->report($exception);
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
        return $this->getContainer()->get(ExceptionHandlerContract::class)->render($request, $exception);
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
        $container  = $this->getContainer();
        $router     = $container->get(RouterContract::class);
        $dispatcher = $container->get(DispatcherContract::class);

        $dispatcher->setCachePath($this->getStoragePath('framework/routes.cache.php'));
        $dispatcher->refreshCache($this->resolvedOptions['env'] !== 'production');

        if (\class_exists(Pipeline::class)) {
            return $this->pipeRequestThroughMiddlewaresAndRouter($request, $router);
        }

        $container->instance(ServerRequestInterface::class, $request);

        return $router->dispatch($request);
    }

    /**
     * Pipes the request through given middlewares and dispatch a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface    $request
     * @param \Viserio\Component\Contracts\Routing\Router $router
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function pipeRequestThroughMiddlewaresAndRouter(
        ServerRequestInterface $request,
        RouterContract $router
    ): ResponseInterface {
        $container = $this->getContainer();

        return (new RoutingPipeline())
            ->setContainer($container)
            ->send($request)
            ->through($this->resolvedOptions['skip_middlewares'] ? [] : $this->middlewares)
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
