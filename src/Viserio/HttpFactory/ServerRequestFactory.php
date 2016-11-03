<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Psr\Http\Message\UriInterface;
use Viserio\Contracts\HttpFactory\ServerRequestFactory as ServerRequestFactoryContract;
use Viserio\Contracts\HttpFactory\ServerRequestGlobalFactory as ServerRequestGlobalFactoryContract;
use Viserio\Http\ServerRequest;
use Viserio\Http\Stream\LazyOpenStream;
use Viserio\Http\Uri;
use Viserio\Http\Util;

class ServerRequestFactory implements ServerRequestFactoryContract, ServerRequestGlobalFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function createServerRequestFromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    ) {
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
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createServerRequest($method, $uri)
    {
        return new ServerRequest($uri, $method);
    }

    /**
     * Get a Uri populated with values from $_SERVER.
     *
     * @return \Psr\Http\Message\UriInterface
     */
    protected function getUriFromGlobals(): UriInterface
    {
        $uri = new Uri('');
        $addSchema = false;

        if (isset($_SERVER['HTTP_HOST'])) {
            $uri = $uri->withHost($_SERVER['HTTP_HOST']);
            $addSchema = true;
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $uri = $uri->withHost($_SERVER['SERVER_NAME']);
            $addSchema = true;
        }

        if ($addSchema) {
            $uri = $uri->withScheme(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http');
        }

        if (isset($_SERVER['SERVER_PORT'])) {
            $uri = $uri->withPort($_SERVER['SERVER_PORT']);
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $uri->withPath(current(explode('?', $_SERVER['REQUEST_URI'])));
        }

        if (isset($_SERVER['QUERY_STRING'])) {
            $uri = $uri->withQuery($_SERVER['QUERY_STRING']);
        }

        return $uri;
    }
}
