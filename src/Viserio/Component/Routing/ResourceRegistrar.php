<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Support\Str;

class ResourceRegistrar
{
    /**
     * The router instance.
     *
     * @var \Viserio\Component\Contracts\Routing\Router
     */
    protected $router;

    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $resourceDefaults = [
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ];

    /**
     * The parameters set for this resource instance.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Singular global parameters.
     *
     * @var bool
     */
    protected static $singularParameters = true;

    /**
     * The global parameter mapping.
     *
     * @var array
     */
    protected static $parameterMap = [];

    /**
     * The verbs used in the resource URIs.
     *
     * @var array
     */
    protected static $verbs = [
        'create' => 'create',
        'edit'   => 'edit',
    ];

    /**
     * Create a new resource registrar instance.
     *
     * @param \Viserio\Component\Contracts\Routing\Router $router
     */
    public function __construct(RouterContract $router)
    {
        $this->router = $router;
    }

    /**
     * Route a resource to a controller.
     *
     * @param string $name
     * @param string $controller
     * @param array  $options
     *
     * @return void
     */
    public function register(string $name, string $controller, array $options = []): void
    {
        if (isset($options['parameters']) && count($this->parameters) === 0) {
            $this->parameters = $options['parameters'];
        }

        // If the resource name contains a slash, we will assume the developer wishes to
        // register these resource routes with a prefix so we will set that up out of
        // the box so they don't have to mess with it. Otherwise, we will continue.
        if (mb_strpos($name, '/') !== false) {
            $this->prefixedResource($name, $controller, $options);

            return;
        }

        // We need to extract the base resource from the resource name.
        $baseResource = explode('.', $name);
        $resource     = end($baseResource);

        // Wildcards for a single or nested resource may be overridden using the wildcards option.
        // Overrides are performed by matching the wildcards key with the resource name. If a key
        // matches a resource name, the value of the wildcard is used instead of the resource name.
        if (isset($options['wildcards'][$resource])) {
            $resource = $options['wildcards'][$resource];
        }

        // Nested resources are supported in the framework, but we need to know what
        // name to use for a place-holder on the route wildcards, which should be
        // the base resources.
        $base = $this->getResourceWildcard($resource);

        $defaults = $this->resourceDefaults;

        foreach ($this->getResourceMethods($defaults, $options) as $m) {
            $this->{'addResource' . ucfirst($m)}($name, $base, $controller, $options);
        }
    }

    /**
     * Get the base resource URI for a given resource.
     *
     * @param string $resource
     * @param array  $options
     *
     * @return string
     */
    public function getResourceUri(string $resource, array $options): string
    {
        if (mb_strpos($resource, '.') === false) {
            return $resource;
        }

        // Once we have built the base URI, we'll remove the parameter holder for this
        // base resource name so that the individual route adders can suffix these
        // paths however they need to, as some do not have any parameters at all.
        $segments = explode('.', $resource);

        $uri = $this->getNestedResourceUri($segments, $options);

        $resource = end($segments);

        if (isset($options['wildcards'][$resource])) {
            $resource = $options['wildcards'][$resource];
        }

        return str_replace('/{' . $this->getResourceWildcard($resource) . '}', '', $uri);
    }

    /**
     * Format a resource parameter for usage.
     *
     * @param string $value
     *
     * @return string
     */
    public function getResourceWildcard(string $value): string
    {
        if (isset($this->parameters[$value])) {
            $value = $this->parameters[$value];
        } elseif (isset(static::$parameterMap[$value])) {
            $value = static::$parameterMap[$value];
        } elseif ($this->parameters === 'singular' || static::$singularParameters) {
            $value = Str::singular($value);
        }

        return str_replace('-', '_', $value);
    }

    /**
     * Set or unset the unmapped global parameters to singular.
     *
     * @param bool $singular
     *
     * @return void
     */
    public static function singularParameters(bool $singular = true): void
    {
        static::$singularParameters = $singular;
    }

    /**
     * Set the action verbs used in the resource URIs.
     *
     * @param array $verbs
     *
     * @return void
     */
    public static function setVerbs(array $verbs)
    {
        static::$verbs = array_merge(static::$verbs, $verbs);
    }

    /**
     * Get the action verbs used in the resource URIs.
     *
     * @return array
     */
    public static function getVerbs(): array
    {
        return static::$verbs;
    }

    /**
     * Set the global parameter mapping.
     *
     * @param array $parameters
     *
     * @return void
     */
    public static function setParameters(array $parameters = []): void
    {
        static::$parameterMap = $parameters;
    }

    /**
     * Get the global parameter map.
     *
     * @return array
     */
    public static function getParameters(): array
    {
        return static::$parameterMap;
    }

    /**
     * Build a set of prefixed resource routes.
     *
     * @param string $name
     * @param string $controller
     * @param array  $options
     *
     * @return void
     */
    protected function prefixedResource(string $name, string $controller, array $options): void
    {
        [$name, $prefix] = $this->getResourcePrefix($name);

        // We need to extract the base resource from the resource name. Nested resources
        // are supported in the framework, but we need to know what name to use for a
        // place-holder on the route parameters, which should be the base resources.
        $callback = function ($me) use ($name, $controller, $options): void {
            $me->resource($name, $controller, $options);
        };

        $this->router->group(compact('prefix'), $callback);
    }

    /**
     * Extract the resource and prefix from a resource name.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getResourcePrefix(string $name): array
    {
        $segments = explode('/', $name);

        // To get the prefix, we will take all of the name segments and implode them on
        // a slash. This will generate a proper URI prefix for us. Then we take this
        // last segment, which will be considered the final resources name we use.
        $prefix = implode('/', array_slice($segments, 0, -1));

        return [end($segments), $prefix];
    }

    /**
     * Get the applicable resource methods.
     *
     * @param array $defaults
     * @param array $options
     *
     * @return array
     */
    protected function getResourceMethods(array $defaults, array $options): array
    {
        if (isset($options['only'])) {
            return array_intersect($defaults, (array) $options['only']);
        } elseif (isset($options['except'])) {
            return array_diff($defaults, (array) $options['except']);
        }

        return $defaults;
    }

    /**
     * Add the index method for a resourceful route.
     *
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array  $options
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    protected function addResourceIndex(string $name, string $base, string $controller, array $options)
    {
        $uri = $this->getResourceUri($name, $options);

        $action = $this->getResourceAction($name, $controller, 'index', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the create method for a resourceful route.
     *
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array  $options
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    protected function addResourceCreate(string $name, string $base, string $controller, array $options)
    {
        $uri = $this->getResourceUri($name, $options) . '/' . static::$verbs['create'];

        $action = $this->getResourceAction($name, $controller, 'create', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the store method for a resourceful route.
     *
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array  $options
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    protected function addResourceStore(string $name, string $base, string $controller, array $options): RouteContract
    {
        $uri = $this->getResourceUri($name, $options);

        $action = $this->getResourceAction($name, $controller, 'store', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the show method for a resourceful route.
     *
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array  $options
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    protected function addResourceShow(string $name, string $base, string $controller, array $options): RouteContract
    {
        $uri = $this->getResourceUri($name, $options) . '/{' . $base . '}';

        $action = $this->getResourceAction($name, $controller, 'show', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the edit method for a resourceful route.
     *
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array  $options
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    protected function addResourceEdit(string $name, string $base, string $controller, array $options): RouteContract
    {
        $uri = $this->getResourceUri($name, $options) . '/{' . $base . '}/' . static::$verbs['edit'];

        $action = $this->getResourceAction($name, $controller, 'edit', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the update method for a resourceful route.
     *
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array  $options
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    protected function addResourceUpdate(string $name, string $base, string $controller, array $options): RouteContract
    {
        $uri = $this->getResourceUri($name, $options) . '/{' . $base . '}';

        $action = $this->getResourceAction($name, $controller, 'update', $options);

        return $this->router->match(['PUT', 'PATCH'], $uri, $action);
    }

    /**
     * Add the destroy method for a resourceful route.
     *
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array  $options
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    protected function addResourceDestroy(string $name, string $base, string $controller, array $options): RouteContract
    {
        $uri = $this->getResourceUri($name, $options) . '/{' . $base . '}';

        $action = $this->getResourceAction($name, $controller, 'destroy', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Get the URI for a nested resource segment array.
     *
     * @param array $segments
     * @param array $options
     *
     * @return string
     */
    protected function getNestedResourceUri(array $segments, array $options): string
    {
        // We will spin through the segments and create a place-holder for each of the
        // resource segments, as well as the resource itself. Then we should get an
        // entire string for the resource URI that contains all nested resources.
        return implode('/', array_map(function ($s) use ($options) {
            $wildcard = $s;

            //If a wildcard for a resource has been set to be overridden
            if (isset($options['wildcards'][$s])) {
                $wildcard = $options['wildcards'][$s];
            }

            return $s . '/{' . $this->getResourceWildcard($wildcard) . '}';
        }, $segments));
    }

    /**
     * Get the action array for a resource route.
     *
     * @param string $resource
     * @param string $controller
     * @param string $method
     * @param array  $options
     *
     * @return array
     */
    protected function getResourceAction(string $resource, string $controller, string $method, array $options): array
    {
        $name = $this->getResourceRouteName($resource, $method, $options);

        $action = ['as' => $name, 'uses' => $controller . '@' . $method];

        if (isset($options['middlewares'])) {
            $action['middlewares'] = $options['middlewares'];
        }

        if (isset($options['bypass'])) {
            $action['bypass'] = $options['bypass'];
        }

        return $action;
    }

    /**
     * Get the name for a given resource.
     *
     * @param string $resource
     * @param string $method
     * @param array  $options
     *
     * @return string
     */
    protected function getResourceRouteName(string $resource, string $method, array $options): string
    {
        $name = $resource;

        // If the names array has been provided to us we will check for an entry in the
        // array first. We will also check for the specific method within this array
        // so the names may be specified on a more "granular" level using methods.
        if (isset($options['names'])) {
            if (is_string($options['names'])) {
                $name = $options['names'];
            } elseif (isset($options['names'][$method])) {
                return $options['names'][$method];
            }
        }

        // If a global prefix has been assigned to all names for this resource, we will
        // grab that so we can prepend it onto the name when we create this name for
        // the resource action. Otherwise we'll just use an empty string for here.
        $prefix = isset($options['as']) ? $options['as'] . '.' : '';

        return trim(sprintf('%s%s.%s', $prefix, $name, $method), '.');
    }
}
