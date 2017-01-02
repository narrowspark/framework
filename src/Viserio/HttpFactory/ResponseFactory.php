<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Interop\Http\Factory\ResponseFactoryInterface;
use Viserio\Http\Response;
use Psr\Http\Message\ResponseInterface;

final class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResponse($code = 200): ResponseInterface
    {
        return new Response(
            $code
        );
    }
}
