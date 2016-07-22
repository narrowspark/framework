<?php
declare(strict_types=1);
namespace Viserio\Http;

use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\Http\ResponseFactory as ResponseFactoryContract;

final class ResponseFactory implements ResponseFactoryContract
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createResponse(
        int $code = 200
    ): ResponseInterface {
        return new Response(
            $code
        );
    }
}
