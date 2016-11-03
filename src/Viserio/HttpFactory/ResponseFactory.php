<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Viserio\Contracts\HttpFactory\ResponseFactory as ResponseFactoryContract;
use Viserio\Http\Response;

final class ResponseFactory implements ResponseFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function createResponse($code = 200)
    {
        return new Response(
            $code
        );
    }
}
