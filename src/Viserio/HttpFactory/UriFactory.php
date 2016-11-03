<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Viserio\Contracts\HttpFactory\UriFactory as UriFactoryContract;
use Viserio\Http\Uri;

final class UriFactory implements UriFactoryContract
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createUri($uri = '')
    {
        return new Uri($uri);
    }
}
