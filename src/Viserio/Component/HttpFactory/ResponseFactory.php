<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\Http\Response;

final class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new Response();

        return $response->withStatus(
            $code,
            $reasonPhrase === '' ? HttpStatus::getReasonPhrase($code) : $reasonPhrase
        );
    }
}
