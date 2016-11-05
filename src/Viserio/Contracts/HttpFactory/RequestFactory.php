<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory;

interface RequestFactory
{
    /**
     * Create a new request.
     *
     * @param string                                $method
     * @param \Psr\Http\Message\UriInterface|string $uri
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function createRequest($method, $uri);
}
