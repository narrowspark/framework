<?php
declare(strict_types=1);
namespace Viserio\Foundation\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Routing\Router;
use Viserio\Contracts\Exception\Handler as HandlerContract;
use Viserio\Config\Manager as ConfigManager;
use Viserio\StaticalProxy\StaticalProxy;
use Viserio\Contracts\Foundation\Emitter as EmitterContract;
use Viserio\Contracts\Foundation\Terminable as TerminableContract;

class Kernel implements TerminableContract
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
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeWithMiddlewares = [];

    /**
     * The application's route without middleware.
     *
     * @var array
     */
    protected $routeWithoutMiddlewares = [];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     * @param \Viserio\Contracts\Routing\Router         $router
     * @param \Viserio\Contracts\Events\Dispatcher      $events
     */
    public function __construct(
        ApplicationContract $app,
        RouterContract $router
    ) {
        $this->app = $app;

        foreach ($this->routeWithMiddlewares as $routeWithMiddleware) {
            $router->withMiddleware($routeWithMiddleware);
        }

        foreach ($this->routeWithoutMiddlewares as $routeWithoutMiddleware) {
            $router->withMiddleware($routeWithoutMiddleware);
        }

        $this->router = $router;
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface|null $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response = null)
    {
        // Passes the request to the container
        $this->getContainer()->share(ServerRequestInterface::class, $request);

        if ($response === null) {
            $response = $this->getResponse();
        }

        $response = $this->handleRequest($request, $response);

        // stop PHP sending a Content-Type automatically
        ini_set('default_mimetype', '');

        if ($this->isEmptyResponse($response)) {
            return $response->withoutHeader('Content-Type')
                ->withoutHeader('Content-Length');
        }

        $this->app->get(EmitterContract::class)->emit($response);

        $this->terminate($request, $response);
    }

    /**
     * Terminate the application.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     */
    public function terminate(ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($this->events !== null) {
            $this->events->trigger('application.terminated', [$request, $response]);
        }

        $this->app->get(HandlerContract::class)->unregister();
    }

    /**
     * Get the Narrowspark application instance.
     *
     * @return \Viserio\Contracts\Foundation\Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Convert request into response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleRequest(ServerServerRequestInterface $request, ResponseInterface $response)
    {

        if ($this->events !== null) {
            $this->events->trigger('request.received', [$request]);
        }

        $response = $this->router->dispatch($request, $response);

        if ($this->events !== null) {
            $this->events->trigger('response.created', [$request, $response]);
        }

        return $response;
    }

    /**
     * Send the given request through the middleware / router.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendRequestThroughRouter($request): ResponseInterface
    {
        $this->app->instance('request', $request);

        StaticalProxy::clearResolvedInstance('request');

        $this->router->dispatch();
    }

    /**
     * Returns true if the provided response must not output a body and false
     * if the response could have a body.
     *
     * @see https://tools.ietf.org/html/rfc7231
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    protected function isEmptyResponse(ResponseInterface $response): bool
    {
        return in_array($response->getStatusCode(), [204, 205, 304]);
    }
}
