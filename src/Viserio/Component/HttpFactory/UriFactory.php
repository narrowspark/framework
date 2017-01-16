<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\Http\Uri;

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
