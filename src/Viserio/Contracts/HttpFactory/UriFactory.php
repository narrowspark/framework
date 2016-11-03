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
     * @return \Psr\Http\Message\UriInterface
     *
     * @throws \InvalidArgumentException
     *  If the given URI cannot be parsed.
     */
    public function createUri($uri = '');
}
