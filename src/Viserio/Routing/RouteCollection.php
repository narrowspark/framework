<?php
declare(strict_types=1);
namespace Viserio\Routing;

use ArrayIterator;
use Closure;
use Countable;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use RuntimeException;
use RapidRoute\RouteParser;
use Viserio\Contracts\{
    Container\Traits\ContainerAwareTrait,
    Routing\RouteCollector as RouteCollectorContract,
    Routing\RouteStrategy as RouteStrategyContract
};
use Viserio\Routing\RouteParser as ViserioRouteParser;

class RouteCollection implements RouteStrategyContract, RouteCollectorContract, Countable, IteratorAggregate
{
    use ContainerAwareTrait;

   /**
     * An array of the routes keyed by method.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * An flattened array of all of the routes.
     *
     * @var array
     */
    protected $allRoutes = [];

     /**
      * @var array
      */
     protected $namedRoutes = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var \Viserio\Routing\RouteGroup[]
     */
    protected $groups = [];

    /**
     * @var \RapidRoute\RouteParser
     */
    protected $parser = [];

    /**
     * @var array
     */
    protected $patternMatchers = [
        '/{(.+?):number}/'        => '{$1:[0-9]+}',
        '/{(.+?):word}/'          => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/'          => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/'          => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+}'
    ];

    /**
     * Constructor.
     *
     * @param \RapidRoute\RouteParser               $parser
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(
        RouteParser $parser,
        ContainerInterface $container
    ) {
        $this->parser = $parser;
        $this->container = $container;
    }

    /**
     * Add a route to the collection.
     *
     * @param string|string[] $method
     * @param string          $route
     * @param callable        $handler
     * @param int             $strategy
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function addRoute($method, $route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        // are we running a single strategy for the collection?
        $strategy = (isset($this->strategy)) ? $this->strategy : $strategy;

        // if the handler is an anonymous function, we need to store it for later use
        // by the dispatcher, otherwise we just throw the handler string at FastRoute
        if ($handler instanceof Closure || (is_object($handler) && is_callable($handler))) {
            $callback = $handler;
            $handler = uniqid('Viserio::route::', true);

            $this->routes[$handler]['callback'] = $callback;
        } elseif (is_object($handler)) {
            throw new RuntimeException('Object controllers must be callable.');
        }

        $this->routes[$handler]['strategy'] = $strategy;

        $route = $this->parseRouteString($route);

        //Check for a route alias starting with @
        $matches = [];

        if (preg_match(ViserioRouteParser::ALIAS_REGEX, $route, $matches)) {
            $route = preg_replace(ViserioRouteParser::ALIAS_REGEX, '', $route);
            $this->namedRoutes[$matches[0]] = $route;

            $handler = [
                'name' => $matches[0],
                'handler' => $handler,
            ];
        }

        parent::addRoute($method, $route, $handler);

        return $this;
    }

    /**
     * Builds a dispatcher based on the routes attached to this collection.
     *
     * @return \Viserio\Routing\Dispatcher
     */
    public function getDispatcher()
    {
        $dispatcher = new Dispatcher($this->container, $this->routes, $this->getData());

        if ($this->strategy !== null) {
            $dispatcher->setStrategy($this->strategy);
        }

        return $dispatcher;
    }

    /**
     * Map a handler to the given methods and route.
     *
     * @param string|array    $route    The route to match against
     * @param string|callable $handler  The handler for the route
     * @param string|string[] $methods  The HTTP methods for this handler
     * @param int             $strategy
     */
    public function map($route, $handler, $methods = 'GET', $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        $this->addRoute($methods, $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to GET HTTP method.
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param int             $strategy
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function get($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute(['GET', 'HEAD'], $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to POST HTTP method.
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param int             $strategy
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function post($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('POST', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to PUT HTTP method.
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param int             $strategy
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function put($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('PUT', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to PATCH HTTP method.
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param int             $strategy
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function patch($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('PATCH', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to DELETE HTTP method.
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param int             $strategy
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function delete($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('DELETE', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to HEAD HTTP method.
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param int             $strategy
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function head($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('HEAD', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to OPTIONS HTTP method.
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param int             $strategy
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function options($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('OPTIONS', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to ANY HTTP method.
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param int             $strategy
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function any($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('ANY', $route, $handler, $strategy);
    }

    /**
     * Add a "before" event listener.
     *
     * @param string   $name
     * @param callable $handler
     * @param int      $priority
     */
    public function onBefore($name, $handler, $priority = 0)
    {
        $this->addEventListener($name, $handler, 'before', $priority);
    }

    /**
     * Add an "after" event listener.
     *
     * @param string   $name
     * @param callable $handler
     * @param int      $priority
     */
    public function onAfter($name, $handler, $priority = 0)
    {
        $this->addEventListener($name, $handler, 'after', $priority);
    }

    /**
     * Add a global "before" event listener.
     *
     * @param callable $handler
     * @param int      $priority
     */
    public function globalOnBefore($handler, $priority = 0)
    {
        $this->addEventListener(null, $handler, 'before', $priority);
    }

    /**
     * Add a global "after" event listener.
     *
     * @param callable $handler
     * @param int      $priority
     */
    public function globalOnAfter($handler, $priority = 0)
    {
        $this->addEventListener(null, $handler, 'after', $priority);
    }

    /**
     * Redirect instance.
     *
     * @return \Viserio\Routing\Redirect
     */
    public function redirect(): Redirect
    {
        return new Redirect($this);
    }

    /**
     * Returns the array of registered named routes (starting with @).
     *
     * @return array
     */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }

    /**
     * Get all of the routes in the collection.
     *
     * @return array
     */
    public function getRoutes()
    {
        return array_values($this->allRoutes);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getRoutes());
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->getRoutes());
    }

    /**
     * @param string|null $name
     * @param callable    $handler
     * @param string      $when
     * @param int         $priority
     */
    protected function addEventListener($name, $handler, $when, $priority)
    {
        if ($name) {
            if (array_key_exists($name, $this->filters)) {
                throw new LogicException(sprintf('Filter with name %s already defined', $name));
            }

            $this->filters[$name] = $name;
        }

        $name = $name ? sprintf('route%s%s', $when, $name) : sprintf('route%s', $when);

        $this->container['events']->addListener($name, $handler, $priority);
    }

    /**
     * Get filter.
     *
     * @param string $name
     */
    protected function getFilter($name)
    {
        if (! array_key_exists($name, $this->filters)) {
            throw new InvalidArgumentException(sprintf('Filter with name %s is not defined', $name));
        }

        return $this->filters[$name];
    }

    /**
     * Convenience method to convert pre-defined key words in to regex strings.
     *
     * @param string $route
     *
     * @return string
     */
    protected function parseRouteString($route)
    {
        $wildcards = [
            '/{(.+?):number}/' => '{$1:[0-9]+}',
            '/{(.+?):word}/' => '{$1:[a-zA-Z]+}',
            '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        ];

        return preg_replace(array_keys($wildcards), array_values($wildcards), $route);
    }
}
