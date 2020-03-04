<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\HttpFoundation;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\HttpFoundation\Event\KernelExceptionEvent;
use Viserio\Component\HttpFoundation\Event\KernelFinishRequestEvent;
use Viserio\Component\HttpFoundation\Event\KernelRequestEvent;
use Viserio\Component\HttpFoundation\Event\KernelTerminateEvent;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Routing\Pipeline as RoutingPipeline;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Contract\HttpFoundation\HttpKernel as HttpKernelContract;
use Viserio\Contract\HttpFoundation\Terminable as TerminableContract;
use Viserio\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Contract\Routing\Router as RouterContract;

class Kernel extends AbstractKernel implements HttpKernelContract, TerminableContract
{
    /**
     * List of allowed bootstrap types.
     *
     * @internal
     */
    protected static array $allowedBootstrapTypes = ['global', 'http'];

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $serverRequest = $serverRequest->withAddedHeader('X-Php-Ob-Level', (string) \ob_get_level());

        $this->bootstrap();

        $container = $this->getContainer();
        $events = null;

        if ($container->has(EventManagerContract::class)) {
            $events = $container->get(EventManagerContract::class);
            $events->trigger(new KernelRequestEvent($this, $serverRequest));
        }

        // Passes the request to the container
        $container->set(ServerRequestInterface::class, $serverRequest);

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
        if (! $this->bootstrapManager->hasBeenBootstrapped()) {
            return;
        }

        $container = $this->getContainer();

        if ($container->has(EventManagerContract::class)) {
            $container->get(EventManagerContract::class)
                ->trigger(new KernelTerminateEvent($this, $serverRequest, $response));
        }
    }

    /**
     * Convert request into response.
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
     */
    protected function sendRequestThroughRouter(ServerRequestInterface $request): ResponseInterface
    {
        $container = $this->getContainer();
        $router = $container->get(RouterContract::class);
        $dispatcher = $container->get(DispatcherContract::class);

        $dispatcher->setCachePath($this->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'routes.cache.php'));
        $dispatcher->refreshCache($this->getEnvironment() !== 'prod');

        if (\class_exists(Pipeline::class)) {
            return $this->pipeRequestThroughMiddlewareAndRouter($request, $router);
        }

        $container->set(ServerRequestInterface::class, $request);

        return $router->dispatch($request);
    }

    /**
     * Pipes the request through given middleware and dispatch a response.
     */
    protected function pipeRequestThroughMiddlewareAndRouter(
        ServerRequestInterface $request,
        RouterContract $router
    ): ResponseInterface {
        $container = $this->getContainer();

        return (new RoutingPipeline())
            ->setContainer($container)
            ->send($request)
            ->through(/* $this->resolvedOptions['skip_middleware'] ? [] : $this->resolvedOptions['middleware'] */[])
            ->then(static function ($request) use ($router, $container) {
                $container->set(ServerRequestInterface::class, $request);

                return $router->dispatch($request);
            });
    }

    /**
     * {@inheritdoc}
     */
    protected function getBootstrapLockFileName(): string
    {
        return 'http_bootstrap.lock';
    }
}
