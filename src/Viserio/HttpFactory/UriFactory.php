<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Psr\Http\Message\UriInterface;
use Interop\Http\Factory\UriFactoryInterface;
use Viserio\Http\Uri;

final class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function createUri($uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
