<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Psr\Http\Message\UriInterface;
use Interop\Http\Factory\UriFactoryInterface;

final class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUri($uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
