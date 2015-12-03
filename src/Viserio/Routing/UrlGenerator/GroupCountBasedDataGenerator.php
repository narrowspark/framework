<?php
namespace Viserio\Routing\UrlGenerator;

use Viserio\Contracts\Routing\DataGenerator as DataGeneratorContract;
use Viserio\Contracts\Routing\RouteCollector as RouteCollectorContract;

class GroupCountBasedDataGenerator implements DataGeneratorContract
{
    /**
     * @var \Viserio\Contracts\Routing\RouteCollector
     */
    private $routeCollector;

    /**
     * Constructor.
     *
     * @param \Viserio\Contracts\Routing\RouteCollector $routeCollector
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
