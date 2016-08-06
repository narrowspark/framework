<?php
declare(strict_types=1);
namespace Viserio\Routing;

use RapidRoute\Route as BaseRoute;
use Viserio\Contracts\Routing\Route as RouteContract;

class Route extends BaseRoute implements RouteContract
{
    /**
     * The URI pattern the route responds to.
     *
     * @var string
     */
    protected $uri;

    /**
     * The HTTP methods the route responds to.
     *
     * @var string|string[]
     */
    protected $httpMethods;

    /**
     * The route action array.
     *
     * @var \Closure|array
     */
    protected $action;

    /**
     * The controller instance.
     *
     * @var mixed
     */
    protected $controller;

    /**
     * The default values for the route.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * The regular expression requirements.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * The array of matched parameters.
     *
     * @var array|null
     */
    protected $parameters;

    /**
     * The parameter names for the route.
     *
     * @var array|null
     */
    protected $parameterNames;
}
