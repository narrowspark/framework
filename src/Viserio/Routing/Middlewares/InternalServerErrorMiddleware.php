<?php
declare(strict_types=1);
namespace Viserio\Routing\Middlewares;

use Narrowspark\HttpStatus\Exception\InternalServerErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Delegate as DelegateContract;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;

class InternalServerErrorMiddleware implements ServerMiddlewareContract
{
    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        DelegateContract $frame
    ): ResponseInterface {
        $response = $frame->next($request);

        throw new InternalServerErrorException();
    }
}
