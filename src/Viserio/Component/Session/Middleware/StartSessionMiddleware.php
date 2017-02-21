<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Middleware;

use Cake\Chronos\Chronos;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Cookie\RequestCookies;
use Viserio\Component\Cookie\SetCookie;
use Viserio\Component\Session\Fingerprint\ClientIpGenerator;
use Viserio\Component\Session\Fingerprint\UserAgentGenerator;
use Viserio\Component\Session\Handler\CookieSessionHandler;
use Viserio\Component\Session\SessionManager;

class StartSessionMiddleware implements MiddlewareInterface
{
    /**
     * The session manager.
     *
     * @var \Viserio\Component\Session\SessionManager
     */
    protected $manager;

    /**
     * Driver config.
     *
     * @var array
     */
    protected $driverConfig = [];

    /**
     * Manager default driver config.
     *
     * @var array|\ArrayAccess
     */
    protected $config = [];

    /**
     * Create a new session middleware.
     *
     * @param \Viserio\Component\Session\SessionManager $manager
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager      = $manager;
        $this->driverConfig = $manager->getDriverConfig($manager->getDefaultDriver());
        $this->config       = $manager->getConfig();
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        // If a session driver has been configured, we will need to start the session
        // so that the data is ready.
        if ($this->isSessionConfigured()) {
            // Note that the Narrowspark sessions do not make use of PHP
            // "native" sessions in any way since they are crappy.
            $session = $this->startSession($request);

            $request = $request->withAttribute('session', $session);

            $this->collectGarbage($session);
        }

        $response = $delegate->process($request);

        // Again, if the session has been configured we will need to close out the session
        // so that the attributes may be persisted to some storage medium. We will also
        // add the session identifier cookie to the application response headers now.
        if ($this->isSessionConfigured()) {
            $this->storeCurrentUrl($request, $session);

            $response = $this->addCookieToResponse($response, $session);

            $session->save();
        }

        return $response;
    }

    /**
     * Start the session for the given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Viserio\Component\Contracts\Session\Store
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
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Viserio\Component\Contracts\Session\Store
     */
    protected function getSession(ServerRequestInterface $request): StoreContract
    {
        $session = $this->manager->getDriver();
        $cookies = RequestCookies::fromRequest($request);

        $session->setId($cookies->get($session->getName()) ?? '');

        $session->addFingerprintGenerator(new ClientIpGenerator($request));
        $session->addFingerprintGenerator(new UserAgentGenerator());

        return $session;
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param \Psr\Http\Message\ServerRequestInterface   $request
     * @param \Viserio\Component\Contracts\Session\Store $session
     */
    protected function storeCurrentUrl(ServerRequestInterface $request, StoreContract $session)
    {
        if ($request->getMethod() === 'GET' &&
            $request->getAttribute('route') &&
            ! $request->getHeaderLine('HTTP_X_REQUESTED_WITH') == 'xmlhttprequest'
        ) {
            $session->setPreviousUrl((string) $request->getUri());
        }
    }

    /**
     * Remove the garbage from the session if necessary.
     *
     * @param \Viserio\Component\Contracts\Session\Store $session
     */
    protected function collectGarbage(StoreContract $session)
    {
        $lottery     = $this->config['lottery'];
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
     * @param \Psr\Http\Message\ResponseInterface        $response
     * @param \Viserio\Component\Contracts\Session\Store $session
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addCookieToResponse(ResponseInterface $response, StoreContract $session): ResponseInterface
    {
        if ($session->getHandler() instanceof CookieSessionHandler) {
            $session->save();

            return $response;
        }

        $config = $this->config;

        $setCookie = new SetCookie(
            $session->getName(),
            $session->getId(),
            $this->getCookieExpirationDate($config),
            $config['path'] ?? '/',
            $config['domain'] ?? null,
            $config['secure'] ?? false,
            $config['http_only'] ?? false,
            $config['same_site'] ?? false
        );

        return $response->withAddedHeader('Set-Cookie', (string) $setCookie);
    }

    /**
     * Get the session lifetime in seconds.
     *
     * @return int
     */
    protected function getSessionLifetimeInSeconds(): int
    {
        // Default 1 day
        $lifetime = $this->config['lifetime'] ?? 1440;

        return Chronos::now()->subMinutes($lifetime)->getTimestamp();
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @param array $config
     *
     * @return int|\Cake\Chronos\Chronos
     */
    protected function getCookieExpirationDate(array $config)
    {
        return ($config['expire_on_close'] ?? false) ?
            0 :
            Chronos::now()->addMinutes($config['lifetime']);
    }

    /**
     * Determine if a session driver has been configured.
     *
     * @return bool
     */
    private function isSessionConfigured(): bool
    {
        return isset($this->config['drivers'][$this->manager->getDefaultDriver()]);
    }
}
