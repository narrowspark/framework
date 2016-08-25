<?php
declare(strict_types=1);
namespace Viserio\Routing\Middlewares;

use Narrowspark\HttpStatus\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Delegate as DelegateContract;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;

class NotFoundMiddleware implements ServerMiddlewareContract
{
    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        DelegateContract $frame
    ): ResponseInterface {
        throw new NotFoundException();
    }
}
