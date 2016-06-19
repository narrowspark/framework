<?php
namespace Viserio\Contracts\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

interface RequestFactory
{
    /**
     * Create a new request.
     *
     * @param string              $method
     * @param UriInterface|string $uri
     *
     * @return RequestInterface
     */
    public function createRequest(
        string $method,
        $uri
    ): RequestInterface;
}
