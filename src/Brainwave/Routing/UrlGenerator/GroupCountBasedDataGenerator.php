<?php

namespace Brainwave\Routing\UrlGenerator;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Routing\DataGenerator as DataGeneratorContract;
use Brainwave\Contracts\Routing\RouteCollector as RouteCollectorContract;

/**
 * GroupCountBasedDataGenerator.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class GroupCountBasedDataGenerator implements DataGeneratorContract
{
    /**
     * @var \Brainwave\Contracts\Routing\RouteCollector
     */
    private $routeCollector;

    /**
     * Constructor.
     *
     * @param \Brainwave\Contracts\Routing\RouteCollector $routeCollector
     */
    public function __construct(RouteCollectorContract $routeCollector)
    {
        $this->routeCollector = $routeCollector;
    }

    /**
     * Get formatted route data for use by a URL generator.
     *
     * @return array
     */
    public function getData()
    {
        $routes = $this->routeCollector->getData();
        $data = [];

        foreach ($routes[0] as $path => $methods) {
            $handler = reset($methods);
            if (is_array($handler) && isset($handler['name'])) {
                $data[$handler['name']] = $path;
            }
        }

        foreach ($routes[1] as $method) {
            foreach ($method as $group) {
                $data = array_merge($data, $this->parseDynamicGroup($group));
            }
        }

        return $data;
    }

    /**
     * Parse a group of dynamic routes.
     *
     * @param $group
     *
     * @return array
     */
    private function parseDynamicGroup($group)
    {
        $regex = $group['regex'];
        $parts = explode('|', $regex);
        $data = [];

        foreach ($group['routeMap'] as $matchIndex => $routeData) {
            if (!is_array($routeData[0]) || !isset($routeData[0]['name']) || !isset($parts[$matchIndex - 1])) {
                continue;
            }

            $parameters = $routeData[1];
            $path = $parts[$matchIndex - 1];

            foreach ($parameters as $parameter) {
                $path = $this->replaceOnce('([^/]+)', '{'.$parameter.'}', $path);
            }

            $path = rtrim($path, '()$~');
            $data[$routeData[0]['name']] = [
                'path' => $path,
                'params' => $parameters,
            ];
        }

        return $data;
    }

    /**
     * Replace the first occurrence of a string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     *
     * @return mixed
     */
    private function replaceOnce($search, $replace, $subject)
    {
        $pos = strpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}
