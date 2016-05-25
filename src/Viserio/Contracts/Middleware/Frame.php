<?php
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface ServerFrameInterface
{
    /**
     *
     * @param  ServerRequestInterface $request [description]
     * @return function                        [description]
     */
    public function next(ServerRequestInterface $request): ResponseInterface;

    /**
     *
     * @return [type] [description]
     */
    public function factory(): FactoryInterface;
}
