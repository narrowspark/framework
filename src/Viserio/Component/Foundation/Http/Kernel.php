<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http;

use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Component\Contract\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Exception\Provider\HttpExceptionServiceProvider;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;
use Viserio\Component\Foundation\Bootstrap\HttpHandleExceptions;
use Viserio\Component\Foundation\Bootstrap\LoadConfiguration;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Bootstrap\RegisterStaticalProxies;
use Viserio\Component\Foundation\BootstrapManager;
use Viserio\Component\Foundation\Http\Event\KernelExceptionEvent;
use Viserio\Component\Foundation\Http\Event\KernelFinishRequestEvent;
use Viserio\Component\Foundation\Http\Event\KernelRequestEvent;
use Viserio\Component\Foundation\Http\Event\KernelTerminateEvent;
use Viserio\Component\Foundation\Provider\ConfigServiceProvider;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Profiler\Middleware\ProfilerMiddleware;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Pipeline as RoutingPipeline;
use Viserio\Component\Routing\Provider\RoutingServiceProvider;
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
    protected $middleware = [];

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
    protected $routeMiddleware = [];

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
        ConfigureKernel::class,
        HttpHandleExceptions::class,
        LoadServiceProvider::class,
    ];

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        $options = [
            'name'             => 'Narrowspark',
            'skip_middleware'  => false,
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
        if (\in_array($middleware, $this->middleware, true) === false) {
            \array_unshift($this->middleware, $middleware);
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
        if (\in_array($middleware, $this->middleware, true) === false) {
            $this->middleware[] = $middleware;
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
        $events    = null;

        if ($container->has(EventManagerContract::class)) {
            $events = $container->get(EventManagerContract::class);
            $events->trigger(new KernelRequestEvent($this, $serverRequest));
        }

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
        $container = $this->getContainer();

        if (! $container->get(BootstrapManager::class)->hasBeenBootstrapped()) {
            return;
        }

        if ($container->has(EventManagerContract::class)) {
            $container->get(EventManagerContract::class)
                ->trigger(new KernelTerminateEvent($this, $serverRequest, $response));
        }
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
            $this->prepareBootstrap();

            $bootstrapManager->bootstrapWith($this->bootstrappers);

            if ($this->isDebug() && ! isset($_ENV['SHELL_VERBOSITY']) && ! isset($_SERVER['SHELL_VERBOSITY'])) {
                \putenv('SHELL_VERBOSITY=3');

                $_ENV['SHELL_VERBOSITY']    = 3;
                $_SERVER['SHELL_VERBOSITY'] = 3;
            }

            $dispatcher = $container->get(DispatcherContract::class);

            if ($dispatcher instanceof MiddlewareBasedDispatcher) {
                $dispatcher->setMiddlewarePriorities($this->middlewarePriority);
                $dispatcher->withMiddleware($this->routeMiddleware);

                foreach ($this->middlewareGroups as $key => $middleware) {
                    $dispatcher->setMiddlewareGroup($key, $middleware);
                }
            }
        }
    }

    /**
     * Convert request into response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface             $serverRequest
     * @param null|\Viserio\Component\Contract\Events\EventManager $events
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleRequest(ServerRequestInterface $serverRequest, ?EventManagerContract $events): ResponseInterface
    {
        try {
            if ($events !== null) {
                $events->trigger(new KernelFinishRequestEvent($this, $serverRequest));
            }

            $response = $this->sendRequestThroughRouter($serverRequest);
        } catch (Throwable $exception) {
            $this->reportException($exception);

            $response = $this->renderException($serverRequest, $exception);

            if ($events !== null) {
                $events->trigger(new KernelExceptionEvent($this, $serverRequest, $response));
            }
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
        $this->getContainer()->get(HttpHandlerContract::class)->report($exception);
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
        return $this->getContainer()->get(HttpHandlerContract::class)->render($request, $exception);
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
        $dispatcher->refreshCache($this->getEnvironment() !== 'prod');

        if (\class_exists(Pipeline::class)) {
            return $this->pipeRequestThroughMiddlewareAndRouter($request, $router);
        }

        $container->instance(ServerRequestInterface::class, $request);

        return $router->dispatch($request);
    }

    /**
     * Pipes the request through given middleware and dispatch a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface   $request
     * @param \Viserio\Component\Contract\Routing\Router $router
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function pipeRequestThroughMiddlewareAndRouter(
        ServerRequestInterface $request,
        RouterContract $router
    ): ResponseInterface {
        $container = $this->getContainer();

        return (new RoutingPipeline())
            ->setContainer($container)
            ->send($request)
            ->through($this->resolvedOptions['skip_middleware'] ? [] : $this->middleware)
            ->then(function ($request) use ($router, $container) {
                $container->instance(ServerRequestInterface::class, $request);

                return $router->dispatch($request);
            });
    }

    protected function registerBaseServiceProviders(): void
    {
        parent::registerBaseServiceProviders();

        $container = $this->getContainer();

        $container->register(new RoutingServiceProvider());
        $container->register(new HttpExceptionServiceProvider());
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

    /**
     * Prepare the BootstrapManager with bootstrappers.
     *
     * @return void
     */
    protected function prepareBootstrap(): void
    {
        $container        = $this->container;
        $bootstrapManager = $container->get(BootstrapManager::class);

        if (\class_exists(Dotenv::class)) {
            $bootstrapManager->addBeforeBootstrapping(ConfigureKernel::class, function (KernelContract $kernel): void {
                (new LoadEnvironmentVariables())->bootstrap($kernel);
            });
        }

        if (\class_exists(ConfigServiceProvider::class)) {
            $bootstrapManager->addBeforeBootstrapping(ConfigureKernel::class, function (KernelContract $kernel): void {
                (new LoadConfiguration())->bootstrap($kernel);
            });
        }

        if (\class_exists(StaticalProxy::class)) {
            $bootstrapManager->addAfterBootstrapping(LoadServiceProvider::class, function (KernelContract $kernel): void {
                (new RegisterStaticalProxies())->bootstrap($kernel);
            });
        }
    }
}
