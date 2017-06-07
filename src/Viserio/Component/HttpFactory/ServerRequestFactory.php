<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\Http\Stream\LazyOpenStream;

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
        return $this->buildServerRequest($server);
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
}
