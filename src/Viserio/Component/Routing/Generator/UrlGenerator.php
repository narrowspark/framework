<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Generator;

use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\Contracts\Routing\Exceptions\RouteNotFoundException;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Support\Traits\MacroableTrait;

class UrlGenerator implements UrlGeneratorContract
{
    use MacroableTrait;

    /**
     * The named parameter defaults.
     *
     * @var array
     */
    public $defaultParameters = [];

    /**
     * The route collection.
     *
     * @var \Viserio\Component\Contracts\Routing\RouteCollection
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
     * @param \Viserio\Component\Contracts\Routing\RouteCollection $routes
     * @param \Psr\Http\Message\ServerRequestInterface             $request
     * @param \Interop\Http\Factory\UriFactoryInterface            $uriFactory
     */
    public function __construct(
        RouteCollectionContract $routes,
        ServerRequestInterface $request,
        UriFactoryInterface $uriFactory
    ) {
        $this->routes     = $routes;
        $this->request    = $request;
        $this->uriFactory = $uriFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if (($route = $this->routes->getByName($name)) !== null) {
            return $this->toRoute($route, $parameters, $referenceType);
        }

        throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route [%s] as such route does not exist.', $name));
    }

    /**
     * Get the URL for a given route instance.
     *
     * @param \Viserio\Component\Contracts\Routing\Route $route
     * @param array                                      $parameters
     * @param int                                        $referenceType
     *
     * @throws \Viserio\Component\Routing\Exceptions\UrlGenerationException
     *
     * @return string
     */
    protected function toRoute(RouteContract $route, array $parameters, int $referenceType): string
    {
        $uri = $this->uriFactory->createUri($route->getUri());

        if (($domain = $route->getHost()) !== null) {
            $uri = $uri->withHost($domain);
        } else {
            $uri = $uri->withHost($this->request->getUri()->getHost());
        }

        $uri = $this->addPortAndSchemeToUri($uri);

        foreach ($parameters as $key => $value) {
            $uri = $uri->withQuery($key . '=' . $value);
        }

        if ($referenceType === self::ABSOLUTE_URL) {
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
    protected function addPortAndSchemeToUri(UriInterface $uri): UriInterface
    {
        $requestUri = $this->request->getUri();
        $secure     = $requestUri->getScheme();
        $port       = $requestUri->getPort();

        $uri = $uri->withScheme($requestUri->getScheme());
        $uri = $uri->withPort($requestUri->getPort());

        return $uri;
    }
}
