<?php
namespace Viserio\Http;

use Psr\Http\Message\UriInterface;
use Viserio\Contracts\Http\UriFactory as UriFactoryContract;

final class UriFactory implements UriFactoryContract
{
    public function createUri($uri): UriInterface
    {
        return new Uri($uri);
    }
}
