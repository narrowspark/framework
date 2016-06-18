<?php
namespace Viserio\Http;

use Psr\Http\Message\RequestInterface;
use Viserio\Contracts\Http\RequestFactory as RequestFactoryContract;

final class RequestFactory implements RequestFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function createRequest(
        string $method = 'GET',
        $uri
    ): RequestInterface {
        return new Request(
            $uri,
            $method
        );
    }
}
