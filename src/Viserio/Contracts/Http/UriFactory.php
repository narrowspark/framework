<?php
declare(strict_types=1);
namespace Viserio\Contracts\Http;

use Psr\Http\Message\UriInterface;

interface UriFactory
{
    /**
     * Create a new URI.
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException If the given URI cannot be parsed.
     *
     * @return UriInterface
     */
    public function createUri(string $uri = ''): UriInterface;
}
