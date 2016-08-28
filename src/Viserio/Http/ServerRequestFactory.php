<?php
declare(strict_types=1);
namespace Viserio\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Contracts\Http\ServerRequestFactory as ServerRequestFactoryContract;
use Viserio\Http\Stream\LazyOpenStream;

class ServerRequestFactory implements ServerRequestFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function createServerRequestFromGlobals(): ServerRequestInterface
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
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createServerRequest(string $method, $uri): ServerRequestInterface
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
        $server = $_SERVER;
        $addProtocol = false;

        if (isset($server['HTTPS'])) {
            $uri = $uri->withScheme($server['HTTPS'] == 'on' ? 'https' : 'http');
        }

        if (isset($server['HTTP_HOST'])) {
            $uri = $uri->withHost($server['HTTP_HOST']);
            $addProtocol = true;
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
            $addProtocol = true;
        }

        if ($addProtocol) {
            $uri = $uri->withScheme(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http');
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
