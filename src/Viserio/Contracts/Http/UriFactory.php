<?php
namespace Viserio\Contracts\Http;

use Psr\Http\Message\UriInterface;

interface UriFactory
{
    /**
     * Create a new URI.
     *
     * @param string $uri
     *
     * @return UriInterface
     *
     * @throws \InvalidArgumentException If the given URI cannot be parsed.
     */
    public function createUri(string $uri = ''): UriInterface;
}
