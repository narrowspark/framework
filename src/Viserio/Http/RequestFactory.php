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
        $uri,
        $method = 'GET',
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ): RequestInterface {
        return new Request(
            $uri,
            $method,
            $headers,
            $body,
            $protocolVersion
        );
    }
}
