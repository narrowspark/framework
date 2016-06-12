<?php
namespace Viserio\Http;

use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\Http\ResponseFactory as ResponseFactoryContract;

final class ResponseFactory implements ResponseFactoryContract
{
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
