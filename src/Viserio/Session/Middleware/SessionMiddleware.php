<?php
namespace Viserio\Session\Middleware;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Session\SessionManager;
use Viserio\Session\Handler\CookieSessionHandler;

class SessionMiddleware implements MiddlewareContract
{
    const DEFAULT_COOKIE = 'nvsession';
    const SESSION_ATTRIBUTE = 'session';

    /**
     * The session manager.
     *
     * @var \Viserio\Session\SessionManager
     */
    protected $manager;

    /**
     * Indicates if the session was handled for the current request.
     *
     * @var bool
     */
    protected $sessionHandled = false;

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
        $this->sessionHandled = true;

        // If a session driver has been configured, we will need to start the session
        // so that the data is ready.
        if ($this->sessionConfigured()) {
            // Note that the Narrowspark sessions do not make use of PHP
            // "native" sessions in any way since they are crappy.
            $session = $this->startSession($request);
        }

        $request->withAttribute(self::SESSION_ATTRIBUTE, $session);

        $response = $frame->next($request);

        return $response;
    }

    /**
     * Start the session for the given request.
     *
     * @param ServerRequestInterface $request
     *
     * @return StoreContract
     */
    protected function startSession(ServerRequestInterface $request): StoreContract
    {
        $session = $this->getSession($request);

        if ($session instanceof CookieSessionHandler) {
            $session->setRequest($request);
        }

        $session->start();

        return $session;
    }

    /**
     * Get the session implementation from the manager.
     *
     * @param ServerRequestInterface $request
     *
     * @return StoreContract
     */
    protected function getSession(ServerRequestInterface $request): StoreContract
    {
        $session = $this->manager->driver();

        $cookies = $request->getCookieParams();
        $key = $session->getConfig()->get('app::key');

        $session->setId($cookies[$session->getName()]);
        $session->addFingerprintGenerator(new ClientIpGenerator($key));
        $session->addFingerprintGenerator(new UserAgentGenerator($key));

        return $session;
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param ResponseInterface $response
     * @param StoreContract     $session
     *
     * @return void
     */
    protected function addCookieToResponse(ResponseInterface $response, StoreContract $session)
    {

    }

    /**
     * Determine if a session driver has been configured.
     *
     * @return bool
     */
    private function sessionConfigured()
    {
        return $this->manager->getConfig()->get('session::driver') !== null;
    }
}
