<?php
declare(strict_types=1);
namespace Viserio\Contracts\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

interface ServerRequestFactory
{
    /**
     * Create a new server request.
     *
     * @param string $method
     * @param UriInterface|string $uri
     *
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri): ServerRequestInterface;

    /**
     * Create a new server request from PHP globals.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequestFromGlobals(): ServerRequestInterface;
}
