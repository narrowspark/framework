<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use InvalidArgumentException;
use UnexpectedValueException;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UploadedFileInterface;
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
        $server  = $this->normalizeServer($server ?? $_SERVER);
        $method = $server['REQUEST_METHOD'] ?? 'GET';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $uri = $this->getUriFromGlobals();

        $serverRequest = new ServerRequest(
            $uri,
            $method,
            $headers,
            new LazyOpenStream('php://input', 'r+'),
            $this->marshalProtocolVersion($server),
            $server
        );

        return $serverRequest
            ->withCookieParams($cookies ?? $_COOKIE)
            ->withQueryParams($query ?? $_GET)
            ->withParsedBody($body ?? $_POST)
            ->withUploadedFiles(Util::normalizeFiles($files ?? $_FILES));
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

    /**
     * Marshal the $_SERVER array
     *
     * Pre-processes and returns the $_SERVER superglobal.
     *
     * @param array $server
     *
     * @return array
     */
    protected function normalizeServer(array $server): array
    {
        // This seems to be the only way to get the Authorization header on Apache
        if (! function_exists('apache_request_headers') ||
            isset($server['HTTP_AUTHORIZATION'])
        ) {
            return $server;
        }

        $headers = apache_request_headers();

        if (isset($headers['Authorization'])) {
            $server['HTTP_AUTHORIZATION'] = $headers['Authorization'];

            return $server;
        }

        if (isset($headers['authorization'])) {
            $server['HTTP_AUTHORIZATION'] = $headers['authorization'];

            return $server;
        }

        return $server;
    }

    /**
     * Return HTTP protocol version (X.Y).
     *
     * @param array $server
     *
     * @throws \UnexpectedValueException
     *
     * @return string
     */
    protected function marshalProtocolVersion(array $server): string
    {
        if (! isset($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }

        if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
            throw new UnexpectedValueException(sprintf(
                'Unrecognized protocol version (%s)',
                $server['SERVER_PROTOCOL']
            ));
        }

        return $matches['version'];
    }
}
