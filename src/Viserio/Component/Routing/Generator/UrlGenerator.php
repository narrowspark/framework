<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Generator;

use Interop\Http\Factory\UriFactoryInterface;
use Narrowspark\Arr\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\Contracts\Routing\Exceptions\RouteNotFoundException;
use Viserio\Component\Contracts\Routing\Exceptions\UrlGenerationException;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Support\Traits\MacroableTrait;
use Viserio\Component\Contracts\Log\Traits\LoggerAwareTrait;

class UrlGenerator implements UrlGeneratorContract
{
    use MacroableTrait;
    use LoggerAwareTrait;

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
     * This array defines the characters (besides alphanumeric ones) that will not be percent-encoded in the path segment of the generated URL.
     *
     * PHP's rawurlencode() encodes all chars except "a-zA-Z0-9-._~" according to RFC 3986. But we want to allow some chars
     * to be used in their literal form (reasons below). Other chars inside the path must of course be encoded, e.g.
     * "?" and "#" (would be interpreted wrongly as query and fragment identifier),
     * "'" and """ (are used as delimiters in HTML).
     *
     * @var array
     */
    protected static $dontEncode = [
        // the slash can be used to designate a hierarchical structure and we want allow using it with this meaning
        // some webservers don't allow the slash in encoded form in the path for security reasons anyway
        // see http://stackoverflow.com/questions/4069002/http-400-if-2f-part-of-get-url-in-jboss
        '%2F' => '/',
        // the following chars are general delimiters in the URI specification but have only special meaning in the authority component
        // so they can safely be used in the path in unencoded form
        '%40' => '@',
        '%3A' => ':',
        // these chars are only sub-delimiters that have no predefined meaning and can therefore be used literally
        // so URI producing applications can use these chars to delimit subcomponents in a path segment without being encoded for better readability
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
        '%3F' => '?',
        '%26' => '&',
        '%23' => '#',
        '%25' => '%',
    ];

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
        // First we will construct the entire URI including the root and query string. Once it
        // has been constructed, we'll make sure we don't have any missing parameters or we
        // will need to throw the exception to let the developers know one was not given.
        $path = $this->addQueryString(
            $this->replaceRouteParameters($route->getUri(), $parameters),
            $parameters
        );

        if (preg_match('/\{.*?\}/', $path)) {
            throw new UrlGenerationException($route);
        }

        // Once we have ensured that there are no missing parameters in the URI we will encode
        // the URI and prepare it for returning to the developer.
        $path = strtr(rawurlencode((string) $path), self::$dontEncode);

        $uri = $this->uriFactory->createUri('/' . ltrim($path, '/'));

        if (($domain = $route->getHost()) !== null) {
            $uri = $uri->withHost($domain);
        } else {
            $uri = $uri->withHost($this->request->getUri()->getHost());
        }

        $uri = $this->addPortAndSchemeToUri($uri, $route);

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
     * @param \Psr\Http\Message\UriInterface             $uri
     * @param \Viserio\Component\Contracts\Routing\Route $route
     *
     * @return \Psr\Http\Message\UriInterface
     */
    protected function addPortAndSchemeToUri(UriInterface $uri, RouteContract $route): UriInterface
    {
        if ($route->isHttpOnly()) {
            $secure = 'http';
            $port   = 80;
        } elseif ($route->isHttpsOnly()) {
            $secure = 'https';
            $port   = 443;
        } else {
            $requestUri = $this->request->getUri();
            $secure     = $requestUri->getScheme();
            $port       = $requestUri->getPort();
        }

        $uri = $uri->withScheme($secure);
        $uri = $uri->withPort($port);

        return $uri;
    }

    /**
     * Replace all of the wildcard parameters for a route path.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceRouteParameters(string $path, array &$parameters): string
    {
        $path = $this->replaceNamedParameters($path, $parameters);

        $path = preg_replace_callback('/\{.*?\}/', function ($match) use (&$parameters) {
            return (empty($parameters) && ! (mb_substr($match[0], -mb_strlen('?}')) === '?}'))
                        ? $match[0]
                        : array_shift($parameters);
        }, $path);

        return trim(preg_replace('/\{.*?\?\}/', '', $path), '/');
    }

    /**
     * Replace all of the named parameters in the path.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceNamedParameters(string $path, array &$parameters): string
    {
        return preg_replace_callback('/\{(.*?)\??\}/', function ($m) use (&$parameters) {
            if (isset($parameters[$m[1]])) {
                return Arr::pull($parameters, $m[1]);
            }

            return $m[0];
        }, $path);
    }

    /**
     * Add a query string to the URI.
     *
     * @param string $uri
     * @param array  $parameters
     *
     * @return string
     */
    protected function addQueryString(string $uri, array $parameters): string
    {
        // If the URI has a fragment we will move it to the end of this URI since it will
        // need to come after any query string that may be added to the URL else it is
        // not going to be available. We will remove it then append it back on here.
        if (! is_null($fragment = parse_url($uri, PHP_URL_FRAGMENT))) {
            $uri = preg_replace('/#.*/', '', $uri);
        }

        $uri .= $this->getRouteQueryString($parameters);

        return is_null($fragment) ? $uri : $uri . "#{$fragment}";
    }

    /**
     * Get the query string for a given route.
     *
     * @param array $parameters
     *
     * @return string
     */
    protected function getRouteQueryString(array $parameters): string
    {
        // First we will get all of the string parameters that are remaining after we
        // have replaced the route wildcards. We'll then build a query string from
        // these string parameters then use it as a starting point for the rest.
        if (count($parameters) == 0) {
            return '';
        }

        $query = http_build_query(
            $keyed = $this->getStringParameters($parameters)
        );

        // Lastly, if there are still parameters remaining, we will fetch the numeric
        // parameters that are in the array and add them to the query string or we
        // will make the initial query string if it wasn't started with strings.
        if (count($keyed) < count($parameters)) {
            $query .= '&' . implode(
                '&', $this->getNumericParameters($parameters)
            );
        }

        return '?' . trim($query, '&');
    }

    /**
     * Get the string parameters from a given list.
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function getStringParameters(array $parameters): array
    {
        return array_filter($parameters, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get the numeric parameters from a given list.
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function getNumericParameters(array $parameters): array
    {
        return array_filter($parameters, 'is_numeric', ARRAY_FILTER_USE_KEY);
    }
}
