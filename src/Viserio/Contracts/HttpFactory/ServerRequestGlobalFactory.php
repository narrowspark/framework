<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory;

interface ServerRequestGlobalFactory
{
    /**
     * Create a new server request from superglobals.
     *
     * If any of the parameters are not supplied, the corresponding superglobal
     * value will be used instead.
     *
     * @param array $server
     * @param array $query
     * @param array $body
     * @param array $cookies
     * @param array $files
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function createServerRequestFromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    );
}
