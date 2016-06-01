<?php
namespace Viserio\Contracts\Http;

use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

interface UriFactory
{
    /**
     * Creates an PSR-7 URI.
     *
     * @param string|UriInterface $uri
     *
     * @return UriInterface
     *
     * @throws InvalidArgumentException If the $uri argument can not be converted into a valid URI.
     */
    public function createUri($uri): UriInterface;
}
