<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory;

interface ServerRequestFactory
{
    /**
     * Create a new server request.
     *
     * @param string                                $method
     * @param \Psr\Http\Message\UriInterface|string $uri
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function createServerRequest($method, $uri);
}
