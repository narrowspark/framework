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
     * @param string              $method
     * @param string|UriInterface $uri
     *
     * @return RequestInterface
     */
    public function createRequest(
        string $method = 'GET',
        $uri
    ): RequestInterface;
}
