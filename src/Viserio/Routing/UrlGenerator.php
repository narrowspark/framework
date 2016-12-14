<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Interop\Http\Factory\UriFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Support\Traits\MacroableTrait;

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
     * @param \Viserio\Routing\RouteCollection          $routes
     * @param \Psr\Http\Message\ServerRequestInterface  $request
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
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        if (! is_null($route = $this->routes->getByName($name))) {
            return $this->toRoute($route, $parameters, $absolute);
        }

        throw new InvalidArgumentException(sprintf('Route [%s] not defined.', $name));
    }

    /**
     * Get the URL for a given route instance.
     *
     * @param \Viserio\Contracts\Routing\Route $route
     * @param array                            $parameters
     * @param bool                             $absolute
     *
     * @throws \Viserio\Routing\Exceptions\UrlGenerationException
     *
     * @return string
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
