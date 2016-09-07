<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Interop\Http\Factory\ResponseFactoryInterface;
use Viserio\Http\Response;

final class ResponseFactory implements ResponseFactoryInterface
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
