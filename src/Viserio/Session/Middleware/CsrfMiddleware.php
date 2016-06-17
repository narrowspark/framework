<?php
namespace Viserio\Session\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;
use Viserio\Session\SessionManager;

class CsrfMiddleware implements MiddlewareContract
{
    /**
     * The session manager.
     *
     * @var \Viserio\Session\SessionManager
     */
    protected $manager;

    /**
     * Create a new session middleware.
     *
     * @param \Viserio\Session\SessionManager $manager
     *
     * @return void
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
    * {@inhertidoc}
     */
    public function handle(ServerRequestInterface $request, FrameContract $frame): ResponseInterface
    {
    }
}
