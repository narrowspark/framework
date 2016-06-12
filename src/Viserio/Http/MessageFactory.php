<?php
namespace Viserio\Http;

use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface,
    StreamInterface
};
use Viserio\Contracts\Http\MessageFactory as MessageFactoryContract;

final class MessageFactory implements MessageFactoryContract
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

    /**
     * {@inheritdoc}
     */
    public function createResponse(
        $statusCode = 200,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ): ResponseInterface {
        return new Response(
            $statusCode,
            $headers,
            $body,
            $protocolVersion
        );
    }
}
