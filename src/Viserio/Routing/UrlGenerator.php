<?php
declare(strict_types=1);
namespace Viserio\Routing;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Support\Traits\MacroableTrait;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Routing\Exceptions\UrlGenerationException;
use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UrlGenerator implements UrlGeneratorContract
{
    use MacroableTrait;

    /**
     * The route collection.
     *
     * @var \Viserio\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * The request instance.
     *
     * @var \Interop\Http\Factory\UriFactoryInterface
     */
    protected $uriFactory;

    /**
     * Create a new URL Generator instance.
     *
     * @param \Viserio\Routing\RouteCollection         $routes
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Interop\Http\Factory\UriFactoryInterface $uriFactory
     */
    public function __construct(
        RouteCollection $routes,
        ServerRequestInterface $request,
        UriFactoryInterface $uriFactory
    ) {
        $this->routes = $routes;
        $this->request = $request;
        $this->uriFactory = $uriFactory;
    }

    /**
     * Get the URL to a named route.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        if (! is_null($route = $this->routes->getByName($name))) {
            return $this->toRoute($route, $parameters, $absolute);
        }

        throw new InvalidArgumentException("Route [{$name}] not defined.");
    }

    /**
     * Get the URL for a given route instance.
     *
     * @param \Viserio\Contracts\Routing\Route $route
     * @param array                            $parameters
     * @param bool                             $absolute
     *
     * @return string
     *
     * @throws \Viserio\Routing\Exceptions\UrlGenerationException
     */
    protected function toRoute(RouteContract $route, array $parameters, bool $absolute): string
    {
        $uri = $this->uriFactory->createUri($route->getUri());

        if (($domain = $route->getDomain()) !== null) {
            $uri = $uri->withHost($domain);
        } else {
            $uri = $uri->withHost($this->request->getUri()->getHost());
        }

        $uri = $this->addPortAndSchemeToUri($uri);

        foreach ($parameters as $key => $value) {
            $uri = $uri->withQuery($key . '=' . $value);
        }

        if ($absolute) {
            return (string) $uri;
        }

        return '/' . ltrim(str_replace(
            $uri->getScheme() . '://' . $uri->getHost(),
            '',
            (string) $uri
        ), '/');
    }

    /**
     * Add the port and scheme to the uri if necessary.
     *
     * @param \Psr\Http\Message\UriInterface $uri
     *
     * @return \Psr\Http\Message\UriInterface
     */
    protected function addPortAndSchemeToUri(UriInterface $uri):UriInterface
    {
        $requestUri = $this->request->getUri();
        $secure = $requestUri->getScheme();
        $port = (int) $requestUri->getPort();

        if (($secure === 'https' && $port === 443) || ($secure !== 'https' && $port === 80)) {
            return $uri;
        }

        $uri = $uri->withScheme('https');
        $uri = $uri->withPort(443);

        return $uri;
    }
}
