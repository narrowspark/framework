<?php
namespace Viserio\Contracts\Middleware;

interface ServerFrameInterface
{
    /**
     * @param ServerRequestInterface $request [description]
     *
     * @return function [description]
     */
    public function next(ServerRequestInterface $request): ResponseInterface;

    /**
     * @return [type] [description]
     */
    public function factory(): FactoryInterface;
}
