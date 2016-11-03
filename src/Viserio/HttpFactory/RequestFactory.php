<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Viserio\Contracts\HttpFactory\RequestFactory as RequestFactoryContract;
use Viserio\Http\Request;

final class RequestFactory implements RequestFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function createRequest($method, $uri)
    {
        return new Request(
            $uri,
            $method
        );
    }
}
