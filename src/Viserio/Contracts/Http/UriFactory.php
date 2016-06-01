<?php
namespace Viserio\Contracts\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

interface UriFactory
{
    /**
     * Creates an PSR-7 URI.
     *
     * @param string|UriInterface $uri
     *
     * @throws InvalidArgumentException If the $uri argument can not be converted into a valid URI.
     *
     * @return UriInterface
     */
    public function createUri($uri): UriInterface;
}
