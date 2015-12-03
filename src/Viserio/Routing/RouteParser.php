<?php
namespace Viserio\Routing;

use FastRoute\RouteParser as FastRouteParser;
use FastRoute\RouteParser\Std;

/**
 * RouteCollection.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class RouteParser extends Std implements FastRouteParser
{
    /**
     * Regex to find the route alias.
     */
    const ALIAS_REGEX = '/^(@[a-zA-Z0-9-_\.]+)/';

    /**
     * Parses the string into an array of segments.
     *
     * "/user/{name}/{id:[0-9]+}"
     *
     * @param string $route
     *
     * @return array
     */
    public function parse($route)
    {
        //Remove possible name in route
        $route = preg_replace(self::ALIAS_REGEX, '', $route);

        return parent::parse($route);
    }
}
