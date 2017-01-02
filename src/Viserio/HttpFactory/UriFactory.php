<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Http\Uri;

final class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createUri($uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
