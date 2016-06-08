<?php
namespace Viserio\Contracts\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

interface RequestFactory
{
    /**
     * Creates a new PSR-7 request.
     *
     * @param string|UriInterface                  $uri
     * @param string                               $method
     * @param array                                $headers
     * @param resource|string|StreamInterface|null $body
     * @param string                               $protocolVersion
     *
     * @return RequestInterface
     */
    public function createRequest(
        $uri,
        $method = 'GET',
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ): RequestInterface;
}
