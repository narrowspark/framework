<?php
namespace Viserio\Http;

use Psr\Http\Message\{
    UriInterface,
    ServerRequestInterface
};
use Viserio\Http\Stream\LazyOpenStream;

class ServerRequestFactory
{
    /**
     * Return a ServerRequest populated with superglobals:
     * $_GET
     * $_POST
     * $_COOKIE
     * $_FILES
     * $_SERVER
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    final public static function createFromGlobals(): ServerRequestInterface
    {
        $server = $_SERVER;
        $method = $server['REQUEST_METHOD'] ?? 'GET';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $uri = self::getUriFromGlobals();
        $body = new LazyOpenStream('php://input', 'r+');
        $protocol = isset($server['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.1';

        $serverRequest = new ServerRequest($uri, $method, $headers, $body, $protocol, $server);

        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(Util::normalizeFiles($_FILES));
    }

    /**
     * Get a Uri populated with values from $_SERVER.
     *
     * @return \Psr\Http\Message\UriInterface
     */
    final public static function getUriFromGlobals(): UriInterface
    {
        $uri = new Uri('');
        $server = $_SERVER;

        if (isset($server['HTTPS'])) {
            $uri = $uri->withScheme($server['HTTPS'] == 'on' ? 'https' : 'http');
        }

        if (isset($server['HTTP_HOST'])) {
            $uri = $uri->withHost($server['HTTP_HOST']);
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        }

        if (isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort($server['SERVER_PORT']);
        }

        if (isset($server['REQUEST_URI'])) {
            $uri = $uri->withPath(current(explode('?', $server['REQUEST_URI'])));
        }

        if (isset($server['QUERY_STRING'])) {
            $uri = $uri->withQuery($server['QUERY_STRING']);
        }

        return $uri;
    }
}
