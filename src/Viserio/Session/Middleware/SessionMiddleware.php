<?php
namespace Viserio\Session\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Cookie\Cookie;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Session\SessionManager;
use Viserio\Session\Handler\CookieSessionHandler;

class SessionMiddleware implements ServerMiddlewareContract
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
    public function process(ServerRequestInterface $request, FrameContract $frame): ResponseInterface
    {
        // If a session driver has been configured, we will need to start the session
        // so that the data is ready.
        if ($this->isSessionConfigured()) {
            // Note that the Narrowspark sessions do not make use of PHP
            // "native" sessions in any way since they are crappy.
            $session = $this->startSession($request);
        }

        $response = $frame->next($request);

        // Again, if the session has been configured we will need to close out the session
        // so that the attributes may be persisted to some storage medium. We will also
        // add the session identifier cookie to the application response headers now.
        if ($this->isSessionConfigured()) {
            $this->collectGarbage($session);

            $response = $this->addCookieToResponse($response, $session);

            $session->save();
        }

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

        if ($session->handlerNeedsRequest()) {
            $session->setRequestOnHandler($request);
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

        $key = $session->getConfig()->get('session::key');

        $session->addFingerprintGenerator(new ClientIpGenerator($key));
        $session->addFingerprintGenerator(new UserAgentGenerator($key));

        return $session;
    }

    /**
     * Remove the garbage from the session if necessary.
     *
     * @param StoreContract $session
     *
     * @return void
     */
    protected function collectGarbage(StoreContract $session)
    {
        $config = $this->manager->getConfig();
        $lottery = $config->get('session::lottery');
        $hitsLottery = random_int(1, $lottery[1]) <= $lottery[0];

        // Here we will see if this request hits the garbage collection lottery by hitting
        // the odds needed to perform garbage collection on any given request. If we do
        // hit it, we'll call this handler to let it delete all the expired sessions.
        if ($hitsLottery) {
            $session->getHandler()->gc($this->getSessionLifetimeInSeconds());
        }
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param ResponseInterface $response
     * @param StoreContract     $session
     *
     * @return ResponseInterface
     */
    protected function addCookieToResponse(ResponseInterface $response, StoreContract $session): ResponseInterface
    {
        if ($session->getHandler() instanceof CookieSessionHandler) {
            $session->save();
            return response;
        }

        $config = $this->manager->getConfig();

        $setCookie = new Cookie(
            $session->getName(),
            $session->getId(),
            $this->getCookieExpirationDate($config),
            $config->get('path'),
            $config->get('domain'),
            $config->get('secure', false),
            $config->get('http_only', false)
        );

        $response = $response->withoutHeader('Set-Cookie');
        $response = $response->withAddedHeader('Set-Cookie', (string) $setCookie);

        return $response;
    }

    /**
     * Get the session lifetime in seconds.
     *
     * @return int
     */
    protected function getSessionLifetimeInSeconds(): int
    {
        // Default 1 day
        $lifetime = $this->manager->getConfig()->get('lifetime', 1440);

        return Carbon::now()->subMinutes($lifetime)->getTimestamp();
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @return int
     */
    protected function getCookieExpirationDate($config): int
    {
        return $config->get('expire_on_close', false) ? 0 : Carbon::now()->addMinutes($config->get('lifetime'));
    }

    /**
     * Determine if a session driver has been configured.
     *
     * @return bool
     */
    private function isSessionConfigured(): bool
    {
        return $this->manager->getConfig()->get('session::driver', null) !== null;
    }
}
