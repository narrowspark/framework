<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Generator;

use Interop\Http\Factory\UriFactoryInterface;
use Narrowspark\Arr\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\Contracts\Log\Traits\LoggerAwareTrait;
use Viserio\Component\Contracts\Routing\Exceptions\RouteNotFoundException;
use Viserio\Component\Contracts\Routing\Exceptions\UrlGenerationException;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Support\Traits\MacroableTrait;

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
     * Returns the target path as relative reference from the base path.
     *
     * Only the URIs path component (no schema, host etc.) is relevant and must be given, starting with a slash.
     * Both paths must be absolute and not contain relative parts.
     * Relative URLs from one resource to another are useful when generating self-contained downloadable document archives.
     * Furthermore, they can be used to reduce the link size in documents.
     *
     * Example target paths, given a base path of "/a/b/c/d":
     * - "/a/b/c/d"     -> ""
     * - "/a/b/c/"      -> "./"
     * - "/a/b/"        -> "../"
     * - "/a/b/c/other" -> "other"
     * - "/a/x/y"       -> "../../x/y"
     *
     * @param string $basePath   The base path
     * @param string $targetPath The target path
     *
     * @return string The relative target path
     */
    public static function getRelativePath(string $basePath, string $targetPath): string
    {
        if ($basePath === $targetPath) {
            return '';
        }

        $sourceDirs = explode('/', isset($basePath[0]) && '/' === $basePath[0] ? mb_substr($basePath, 1) : $basePath);
        $targetDirs = explode('/', isset($targetPath[0]) && '/' === $targetPath[0] ? mb_substr($targetPath, 1) : $targetPath);

        array_pop($sourceDirs);

        $targetFile = array_pop($targetDirs);

        foreach ($sourceDirs as $i => $dir) {
            if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
                unset($sourceDirs[$i], $targetDirs[$i]);
            } else {
                break;
            }
        }

        $targetDirs[] = $targetFile;
        $path         = str_repeat('../', count($sourceDirs)) . implode('/', $targetDirs);

        // A reference to the same base directory or an empty subdirectory must be prefixed with "./".
        // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
        // as the first segment of a relative-path reference, as it would be mistaken for a scheme name
        // (see http://tools.ietf.org/html/rfc3986#section-4.2).
        return '' === $path || '/' === $path[0]
            || false !== ($colonPos = mb_strpos($path, ':')) && ($colonPos < ($slashPos = mb_strpos($path, '/')) || false === $slashPos)
            ? "./$path" : $path;
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
        $parameters = array_replace($route->getParameters(), $parameters);

        $parameters = array_filter($parameters, function ($value) {
            return ! empty($value);
        });

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

        $path = $this->replacePathSegments($path);

        $uri = $this->uriFactory->createUri('/' . ltrim($path, '/'));

        if (($host = $route->getDomain()) !== null) {
            $uri = $uri->withHost($host);
        } else {
            $uri = $uri->withHost($this->request->getUri()->getHost());
        }

        $requiredSchemes = false;
        $requestScheme   = $this->request->getUri()->getScheme();

        if ($route->isHttpOnly()) {
            $requiredSchemes = $requestScheme !== 'http';
        } elseif ($route->isHttpsOnly()) {
            $requiredSchemes = $requestScheme !== 'https';
        }

        if ($referenceType === self::ABSOLUTE_URL || $requiredSchemes || $referenceType === self::NETWORK_PATH) {
            $uri = $this->addPortAndSchemeToUri($uri, $route);
        }

        if ($referenceType === self::ABSOLUTE_URL || $requiredSchemes) {
            return (string) $uri;
        } elseif ($referenceType === self::NETWORK_PATH) {
            $uri = $uri->withScheme('');

            return (string) $uri;
        }

        return '/' . self::getRelativePath('//' . $uri->getHost() . '/', (string) $uri);
    }

    /**
     * The path segments "." and ".." are interpreted as relative reference when resolving a URI;
     * see http://tools.ietf.org/html/rfc3986#section-3.3 so we need to encode them as they are not used for this purpose here
     * otherwise we would generate a URI that, when followed by a user agent (e.g. browser), does not match this route.
     *
     * @param string $path
     *
     * @return string
     */
    protected function replacePathSegments(string $path): string
    {
        $path = strtr($path, ['/../' => '/%2E%2E/', '/./' => '/%2E/']);

        if ('/..' === mb_substr($path, -3)) {
            $path = mb_substr($path, 0, -2) . '%2E%2E';
        } elseif ('/.' === mb_substr($path, -2)) {
            $path = mb_substr($path, 0, -1) . '%2E';
        }

        return $path;
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
            if (empty($parameters) && ! (mb_substr($match[0], -mb_strlen('?}')) === '?}')) {
                return $match[0];
            }

            return array_shift($parameters);
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
            if (isset($parameters[$m[1]]) && ! empty($parameters[$m[1]])) {
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

        if (is_null($fragment)) {
            return $uri;
        }

        return $uri . '#' . strtr(rawurlencode($fragment), ['%2F' => '/', '%3F' => '?']);
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
        if (count($parameters) == 0 || in_array(null, $parameters, true)) {
            return '';
        }

        $query = http_build_query(
            $keyed = $this->getStringParameters($parameters),
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        // Lastly, if there are still parameters remaining, we will fetch the numeric
        // parameters that are in the array and add them to the query string or we
        // will make the initial query string if it wasn't started with strings.
        if (count($keyed) < count($parameters)) {
            $query .= '&' . implode(
                '&',
                $this->getNumericParameters($parameters)
            );
        }

        // "/" and "?" can be left decoded for better user experience, see
        // http://tools.ietf.org/html/rfc3986#section-3.4
        return '?' . trim(strtr($query, ['%2F' => '/']), '&');
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
