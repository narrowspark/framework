<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Contract\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\BootstrapManager;
use Viserio\Component\HttpFoundation\Event\KernelExceptionEvent;
use Viserio\Component\HttpFoundation\Event\KernelFinishRequestEvent;
use Viserio\Component\HttpFoundation\Event\KernelRequestEvent;
use Viserio\Component\HttpFoundation\Event\KernelTerminateEvent;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Pipeline as RoutingPipeline;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Kernel extends AbstractKernel implements HttpKernelContract, TerminableContract, RequiresValidatedConfigContract
{
    /**
     * List of allowed bootstrap types.
     *
     * @internal
     *
     * @var array
     */
    protected static $allowedBootstrapTypes = ['global', 'http'];

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        $options = [
            'name'                => 'Narrowspark',
            'skip_middleware'     => false,
            'middleware'          => [],
            'route_middleware'    => [],
            'middleware_groups'   => [],
            'middleware_priority' => [],
        ];

        return \array_merge(parent::getDefaultOptions(), $options);
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'middleware'          => ['array'],
            'route_middleware'    => ['array'],
            'middleware_groups'   => ['array'],
            'middleware_priority' => ['array'],
        ];
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
            $bootstraps = [];

            foreach ($this->getPreparedBootstraps() as $classes) {
                /** @var \Viserio\Component\Contract\Foundation\BootstrapState $class */
                foreach ($classes as $class) {
                    if (\in_array(BootstrapStateContract::class, \class_implements($class), true)) {
                        $method = 'add' . $class::getType() . 'Bootstrapping';

                        $bootstrapManager->{$method}($class::getBootstrapper(), [$class, 'bootstrap']);
                    } else {
                        /** @var \Viserio\Component\Contract\Foundation\Bootstrap $class */
                        $bootstraps[] = $class;
                    }
                }
            }

            $bootstrapManager->bootstrapWith($bootstraps);

            if ($this->isDebug() && ! isset($_ENV['SHELL_VERBOSITY']) && ! isset($_SERVER['SHELL_VERBOSITY'])) {
                \putenv('SHELL_VERBOSITY=3');

                $_ENV['SHELL_VERBOSITY']    = 3;
                $_SERVER['SHELL_VERBOSITY'] = 3;
            }

            $dispatcher = $container->get(DispatcherContract::class);

            if ($dispatcher instanceof MiddlewareBasedDispatcher) {
                $dispatcher->setMiddlewarePriorities($this->resolvedOptions['middleware_priority']);
                $dispatcher->withMiddleware($this->resolvedOptions['route_middleware']);

                foreach ($this->resolvedOptions['middleware_groups'] as $key => $middleware) {
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
    protected function handleRequest(
        ServerRequestInterface $serverRequest,
        ?EventManagerContract $events
    ): ResponseInterface {
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
        $container = $this->getContainer();

        if ($container->has(HttpHandlerContract::class)) {
            $container->get(HttpHandlerContract::class)->report($exception);
        }
    }

    /**
     * Render the exception to a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function renderException(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $container = $this->getContainer();

        if ($container->has(HttpHandlerContract::class)) {
            return $container->get(HttpHandlerContract::class)->render($request, $exception);
        }

        throw $exception;
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

        $dispatcher->setCachePath($this->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'routes.cache.php'));
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
            ->through($this->resolvedOptions['skip_middleware'] ? [] : $this->resolvedOptions['middleware'])
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
