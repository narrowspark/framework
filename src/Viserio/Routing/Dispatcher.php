<?php
namespace Viserio\Routing;

use Closure;
use Exception;
use FastRoute\Dispatcher as FastDispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use Interop\Container\ContainerInterface as ContainerContract;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Viserio\Contracts\Http\Response as ResponseContract;
use Viserio\Contracts\Routing\RouteStrategy as RouteStrategyContract;
use Viserio\Http\JsonResponse;
use Viserio\Http\Response;

class Dispatcher extends GroupCountBasedDispatcher implements RouteStrategyContract
{
    /*
     * Route strategy functionality
     */
    use RouteStrategyTrait;

    /**
     * Container instance.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * All routes.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * [$cache description].
     *
     * @var bool
     */
    protected $cache;

    /**
     * Cache folder path for cached routes.
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Cached routes.
     *
     * @var array
     */
    protected $cahcedRoutes = [];

    /**
     * Constructor.
     *
     * @param ContainerContract $container
     * @param array             $routes
     * @param array             $data
     */
    public function __construct(ContainerContract $container, array $routes, array $data)
    {
        $this->container = $container;
        $this->routes = $routes;

        parent::__construct($data);
    }

    /**
     * Match and dispatch a route matching the given http method and uri.
     *
     * @param string $method
     * @param string $uri
     *
     * @return ResponseContract
     */
    public function dispatch($method, $uri)
    {
        $match = parent::dispatch($method, $uri);

        switch ($match[0]) {
            case FastDispatcher::NOT_FOUND:
                return $this->handleNotFound();

            case FastDispatcher::METHOD_NOT_ALLOWED:
                $allowed = (array) $match[1];

                return $this->handleNotAllowed($allowed);

            case FastDispatcher::FOUND:
            default:
                $handler = (isset($this->routes[$match[1]]['callback'])) ?
                            $this->routes[$match[1]]['callback'] :
                            $match[1];

                $strategy = $this->routes[$match[1]]['strategy'];
                $vars = (array) $match[2];

                return $this->handleFound($handler, $strategy, $vars);
        }
    }

    /**
     * Handle dispatching of a found route.
     *
     * @param string|\Closure                               $handler
     * @param int|\Viserio\Contracts\Routing\CustomStrategy $strategy
     * @param array                                         $vars
     *
     * @throws \RuntimeException
     *
     * @return ResponseContract
     */
    protected function handleFound($handler, $strategy, array $vars = [])
    {
        if ($this->getStrategy() === null) {
            $this->setStrategy($strategy);
        }

        $controller = $this->isController($handler);

        // handle getting of response based on strategy
        if (is_int($strategy)) {
            return $this->getResponseOnStrategy($controller, $strategy, $vars);
        }

        $traits = class_uses($strategy, true);

        // dispatch via strategy
        if (isset($traits['Viserio\Container\ContainerAwareTrait'])) {
            $strategy->setContainer($this->container);
        }

        // we must be using a custom strategy
        return $strategy->dispatch($controller, $vars);
    }

    /**
     * Check if handler is a controller.
     *
     * @param string|\Closure $handler
     *
     * @throws \RuntimeException
     *
     * @return \Closure|string|array
     */
    protected function isController($handler)
    {
        $controller = null;

        // figure out what the controller is
        if (($handler instanceof Closure) || is_callable($handler)) {
            $controller = $handler;
        }

        if (is_string($handler) && strpos($handler, '::') !== false) {
            $controller = explode('::', $handler);
        }

        // if controller method wasn't specified, throw exception.
        if (!$controller) {
            throw new RuntimeException('A class method must be provided as a controller. ClassName::methodName');
        }

        return $controller;
    }

    /**
     * Handle getting of response based on strategy.
     *
     * @param \Viserio\Contracts\Http\Response $controller
     * @param int                              $strategy
     * @param array                            $vars
     *
     * @return ResponseContract
     */
    protected function getResponseOnStrategy($controller, $strategy, $vars)
    {
        switch ($strategy) {
            case RouteStrategyContract::URI_STRATEGY:
                $response = $this->handleUriStrategy($controller, $vars);
                break;
            case RouteStrategyContract::RESTFUL_STRATEGY:
                $response = $this->handleRestfulStrategy($controller, $vars);
                break;
            case RouteStrategyContract::REQUEST_RESPONSE_STRATEGY:
            default:
                $response = $this->handleRequestResponseStrategy($controller, $vars);
                break;
        }

        return $response;
    }

    /**
     * Invoke a controller action.
     *
     * @param ResponseContract $controller
     * @param array            $vars
     *
     * @return ResponseContract
     */
    public function invokeController($controller, array $vars = [])
    {
        if (is_array($controller)) {
            $controller = [
                $this->container[$controller[0]],
                $controller[1],
            ];
        }

        return call_user_func_array($controller, array_values($vars));
    }

    /**
     * Handles response to Request -> Response Strategy based routes.
     *
     * @param ResponseContract $controller
     * @param array            $vars
     *
     * @return ResponseContract
     */
    protected function handleRequestResponseStrategy($controller, array $vars = [])
    {
        $response = $this->invokeController($controller, [
            $this->container->get('request'),
            $this->container->get('response'),
            $vars,
        ]);

        if ($response instanceof ResponseContract) {
            return $response;
        }

        throw new RuntimeException(
            'When using the Request -> Response Strategy your controller must return an instance of [Viserio\Contracts\Http\Response]'
        );
    }

    /**
     * Handles response to Restful Strategy based routes.
     *
     * @param ResponseContract $controller
     * @param array            $vars
     *
     * @return JsonResponse
     */
    protected function handleRestfulStrategy($controller, array $vars = [])
    {
        try {
            $response = $this->invokeController($controller, [
                $this->container['request'],
                $vars,
            ]);

            if ($response instanceof JsonResponse) {
                return $response;
            }

            if (is_array($response) || $response instanceof \ArrayObject) {
                return new JsonResponse($response);
            }

            throw new RuntimeException(
                'Your controller action must return a valid response for the Restful Strategy Acceptable responses are of type: [Array], [ArrayObject] and [Viserio\Http\JsonResponse]'
            );
        } catch (HttpException $exception) {
            $body = [
                'status_code' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
            ];

            return new JsonResponse($body, $exception->getStatusCode(), $exception->getHeaders());
        }
    }

    /**
     * Handles response to URI Strategy based routes.
     *
     * @param ResponseContract $controller
     * @param array            $vars
     *
     * @return ResponseContract
     */
    protected function handleUriStrategy($controller, array $vars)
    {
        $response = $this->invokeController($controller, $vars);

        if ($response instanceof ResponseContract) {
            return $response;
        }

        try {
            $response = new Response($response);
        } catch (Exception $exception) {
            throw new RuntimeException('Unable to build Response from controller return value', 0, $exception);
        }

        return $response;
    }

    /**
     * Handle a not found route.
     *
     * @throws HttpException\NotFoundException
     *
     * @return JsonResponse
     */
    protected function handleNotFound()
    {
        $exception = new HttpException\NotFoundException();

        if ($this->getStrategy() === RouteStrategyContract::RESTFUL_STRATEGY) {
            return $exception->getJsonResponse();
        }

        throw $exception;
    }

    /**
     * Handles a not allowed route.
     *
     * @param array $allowed
     *
     * @throws HttpException\MethodNotAllowedException
     *
     * @return JsonResponse
     */
    protected function handleNotAllowed(array $allowed)
    {
        $exception = new HttpException\MethodNotAllowedException($allowed);

        if ($this->getStrategy() === RouteStrategyContract::RESTFUL_STRATEGY) {
            return $exception->getJsonResponse();
        }

        throw $exception;
    }
}
