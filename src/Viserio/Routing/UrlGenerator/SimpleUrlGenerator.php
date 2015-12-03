<?php
namespace Viserio\Routing\UrlGenerator;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Viserio\Contracts\Routing\DataGenerator as DataGeneratorContract;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;

class SimpleUrlGenerator implements UrlGeneratorContract
{
    /**
     * @var \Viserio\Contracts\Routing\DataGenerator
     */
    protected $dataGenerator;

    protected $initialized = false;

    protected $routes = [];

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param \Viserio\Contracts\Routing\DataGenerator $dataGenerator
     */
    public function __construct(DataGeneratorContract $dataGenerator)
    {
        $this->dataGenerator = $dataGenerator;
    }

    /**
     * Generate a URL for the given route.
     *
     * @param string $name       The name of the route to generate a url for
     * @param array  $parameters Parameters to pass to the route
     * @param bool   $absolute   If true, the generated route should be absolute
     *
     * @return string
     */
    public function generate($name, array $parameters = [], $absolute = false)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $alias = strpos($name, '@') === false ? '@'.$name : $name;

        $path = $this->routes[$alias];

        if (is_array($path)) {
            $params = $path['params'];
            $path = $path['path'];

            foreach ($params as $param) {
                if (!isset($parameters[$param])) {
                    throw new \RuntimeException('Missing required parameter "'.$param.'". Optional parameters not currently supported');
                }

                $path = str_replace('{'.$param.'}', $parameters[$param], $path);
            }
        }

        if ($this->request) {
            $path = $this->request->getBaseUrl().$path;
            if ($absolute) {
                $path = $this->request->getSchemeAndHttpHost().$path;
            }
        }

        return $path;
    }

    /**
     * @param null|\Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest(SymfonyRequest $request = null)
    {
        $this->request = $request;
    }

    /**
     * Initialize the generator.
     */
    protected function initialize()
    {
        $this->routes = $this->dataGenerator->getData();
        $this->initialized = true;
    }
}
