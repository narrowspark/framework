<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\Http\Stream\LazyOpenStream;
use Viserio\Component\Http\Uri;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createServerRequest($method, $uri): ServerRequestInterface
    {
        return $this->buildServerRequest([], [], $method, $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function createServerRequestFromArray(array $server): ServerRequestInterface
    {
        $headers = $this->getHeaders($server);
        $method  = $server['REQUEST_METHOD'] ?? 'GET';

        return $this->buildServerRequest($server, $headers, $method, Uri::createFromServer($server));
    }

    /**
     * Build a server request from given data.
     *
     * @param array                                 $server
     * @param array                                 $headers
     * @param string                                $method
     * @param \Psr\Http\Message\UriInterface|string $uri
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function buildServerRequest(array $server, array $headers, string $method, $uri = null): ServerRequestInterface
    {
        return new ServerRequest(
            $uri,
            $method,
            $headers,
            new LazyOpenStream('php://input', 'r+'),
            $this->marshalProtocolVersion($server),
            $server
        );
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
    protected function getHeaders(array $server): array
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
                $headers['HTTP_AUTHORIZATION'] = $server['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($server['PHP_AUTH_USER'])) {
                $basicPass                     = $server['PHP_AUTH_PW'] ?? '';
                $headers['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode($server['PHP_AUTH_USER'] . ':' . $basicPass);
            } elseif (isset($server['PHP_AUTH_DIGEST'])) {
                $headers['HTTP_AUTHORIZATION'] = $server['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}
