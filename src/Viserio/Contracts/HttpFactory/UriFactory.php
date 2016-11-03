<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory;

interface UriFactory
{
    /**
     * Create a new URI.
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException
     *                                   If the given URI cannot be parsed.
     *
     * @return \Psr\Http\Message\UriInterface
     */
    public function createUri($uri = '');
}
