<?php
declare(strict_types=1);
namespace Viserio\Foundation\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Exception\Handler as HandlerContract;
use Viserio\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Contracts\Foundation\Terminable as TerminableContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Foundation\Bootstrap\ConfigureLogging;
use Viserio\Foundation\Bootstrap\DetectEnvironment;
use Viserio\Foundation\Bootstrap\HandleExceptions;
use Viserio\Foundation\Bootstrap\LoadCommands;
use Viserio\Foundation\Bootstrap\LoadConfiguration;
use Viserio\Foundation\Bootstrap\LoadRoutes;
use Viserio\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Foundation\Bootstrap\RegisterStaticalProxys;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\Routing\Router;
use Viserio\StaticalProxy\StaticalProxy;

class Kernel implements TerminableContract, KernelContract
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
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        LoadConfiguration::class,
        DetectEnvironment::class,
        HandleExceptions::class,
        ConfigureLogging::class,
        RegisterStaticalProxys::class,
        LoadCommands::class,
        LoadRoutes::class,
        LoadServiceProvider::class,
    ];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     * @param \Viserio\Contracts\Routing\Router         $router
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
    public function handle(ServerRequestInterface $request, ResponseInterface $response = null): ResponseInterface
    {
        // Passes the request to the container
        $this->app->instance(ServerRequestInterface::class, $request);
        StaticalProxy::clearResolvedInstance('request');

        if ($this->events !== null) {
            $this->events->trigger(self::REQUEST, [$request]);
        }

        if ($response === null) {
            $response = (new ResponseFactory())->createResponse();
        }

        $this->app->instance(ResponseInterface::class, $response);

        StaticalProxy::clearResolvedInstance('response');

        $response = $this->handleRequest($request, $response);

        // stop PHP sending a Content-Type automatically
        ini_set('default_mimetype', '');

        return $response;
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
            $this->events->trigger(self::TERMINATE, [$request, $response]);
        }

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
    protected function handleRequest(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->bootstrap();

        $router = $this->router;
        $config = $this->app->get(ConfigManager::class);

        $router->setCachePath($config->get('routing.path'));
        $router->refreshCache($config->get('app.env', 'production') === 'production' ? false : true);

        try {
            $response = $router->dispatch($request, $response);

            if ($this->events !== null) {
                $this->events->trigger(self::RESPONSE, [$request, $response]);
            }
        } catch (Throwable $exception) {
            if ($this->events !== null) {
                $this->events->trigger(self::EXCEPTION, [$request, $response]);
            }

            $exceptionHandler = $this->app->get(HandlerContract::class);

            $exceptionHandler->report($exception = new FatalThrowableError($exception));

            $response = $exceptionHandler->render($request, $exception);
        }

        return $response;
    }
}
