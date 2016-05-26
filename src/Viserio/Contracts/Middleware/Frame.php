<?php
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Frame
{
    /**
     * @param ServerRequestInterface $request [description]
     *
     * @return ResponseInterface
     */
    public function next(ServerRequestInterface $request): ResponseInterface;

    /**
     * @return Factory
     */
    public function factory(): Factory;
}
