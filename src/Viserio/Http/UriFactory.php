<?php

declare(strict_types=1);
namespace Viserio\Http;

use Psr\Http\Message\UriInterface;
use Viserio\Contracts\Http\UriFactory as UriFactoryContract;

final class UriFactory implements UriFactoryContract
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
