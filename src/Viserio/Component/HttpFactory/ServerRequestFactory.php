<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\Http\Stream\LazyOpenStream;
use Viserio\Component\Http\Uri;
use Viserio\Component\Http\Util;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createServerRequest(array $server, $method = null, $uri = null): ServerRequestInterface
    {
        $server        = $this->normalizeServer($server);
        $requestMethod = $method ?? $server['REQUEST_METHOD'] ?? 'GET';
        $headers       = function_exists('allheaders') ? allheaders() : $this->allHeaders($server);
        $uri           = $uri ?? Uri::createFromServer($server);

        $serverRequest = new ServerRequest(
            $uri,
            $requestMethod,
            $headers,
            new LazyOpenStream('php://input', 'r+'),
            $this->marshalProtocolVersion($server),
            $server
        );

        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(Util::normalizeFiles($_FILES));
    }

    /**
     * Marshal the $_SERVER array.
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

    /**
     * Get all HTTP header key/values as an associative array for the current request.
     *
     * @param array $server
     *
     * @return array
     */
    protected function allHeaders(array $server): array
    {
        $headers = [];
        $content = [
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
            'CONTENT_TYPE'   => 'Content-Type',
        ];

        foreach ($server as $key => $value) {
            if (mb_substr($key, 0, 5) === 'HTTP_') {
                $key = mb_substr($key, 5);

                if (! isset($content[$key]) || ! isset($server[$key])) {
                    $key           = str_replace(' ', '-', ucwords(mb_strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($content[$key])) {
                $headers[$content[$key]] = $value;
            }
        }

        if (! isset($headers['Authorization'])) {
            if (isset($server['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $server['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($server['PHP_AUTH_USER'])) {
                $basicPass                = isset($server['PHP_AUTH_PW']) ? $server['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($server['PHP_AUTH_USER'] . ':' . $basicPass);
            } elseif (isset($server['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $server['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}
