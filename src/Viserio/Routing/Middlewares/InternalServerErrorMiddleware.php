<?php
declare(strict_types=1);
namespace Viserio\Routing\Middlewares;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Narrowspark\HttpStatus\Exception\InternalServerErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class InternalServerErrorMiddleware implements ServerMiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $frame
    ): ResponseInterface {
        throw new InternalServerErrorException();
    }
}
