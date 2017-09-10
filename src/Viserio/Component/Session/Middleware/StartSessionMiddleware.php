<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Middleware;

use Cake\Chronos\Chronos;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Session\Store as StoreContract;
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
     * @var array
     */
    protected $config = [];

    /**
     * List of fingerprint generators.
     *
     * @var array
     */
    private $fingerprintGenerators = [
        ClientIpGenerator::class,
        UserAgentGenerator::class,
    ];

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
        $session = null;

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
            $session = $this->storeCurrentUrl($request, $session);

            $response = $this->addCookieToResponse($request, $response, $session);
        }

        return $response;
    }

    /**
     * Start the session for the given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Viserio\Component\Contract\Session\Store
     */
    protected function startSession(ServerRequestInterface $request): StoreContract
    {
        $session    = $this->manager->getDriver();
        $cookies    = RequestCookies::fromRequest($request);
        $hasCookie  = $cookies->has($session->getName());

        $session->setId($hasCookie ? $cookies->get($session->getName())->getValue() : '');

        foreach ($this->fingerprintGenerators as $fingerprintGenerator) {
            $session->addFingerprintGenerator(new $fingerprintGenerator($request));
        }

        if ($session->handlerNeedsRequest()) {
            $session->setRequestOnHandler($request);
        }

        if (! $session->open()) {
            $session->start();
        }

        return $session;
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Viserio\Component\Contract\Session\Store $session
     *
     * @return \Viserio\Component\Contract\Session\Store
     */
    protected function storeCurrentUrl(ServerRequestInterface $request, StoreContract $session): StoreContract
    {
        if ($request->getMethod() === 'GET' &&
            $request->getAttribute('_route') &&
            $request->getHeaderLine('X-Requested-With') !== 'XMLHttpRequest'
        ) {
            $session->setPreviousUrl((string) $request->getUri());
        }

        return $session;
    }

    /**
     * Remove the garbage from the session if necessary.
     *
     * @param \Viserio\Component\Contract\Session\Store $session
     */
    protected function collectGarbage(StoreContract $session): void
    {
        $lottery     = $this->config['lottery'];
        $hitsLottery = \random_int(1, $lottery[1]) <= $lottery[0];

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
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param \Viserio\Component\Contract\Session\Store $session
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addCookieToResponse(ServerRequestInterface $request, ResponseInterface $response, StoreContract $session): ResponseInterface
    {
        if ($session->getHandler() instanceof CookieSessionHandler) {
            $session->save();
        }

        $config = $this->config;
        $uri    = $request->getUri();

        $setCookie = new SetCookie(
            $session->getName(),
            $session->getId(),
            $this->getCookieExpirationDate($config),
            $config['path'] ?? '/',
            $config['domain'] ?? $uri->getHost(),
            $config['secure'] ?? ($uri->getScheme() === 'https'),
            $config['http_only'] ?? true,
            $config['samesite'] ?? false
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
        $lifetime = $this->config['lifetime'] ?? 86400;

        return Chronos::now()->subSeconds($lifetime)->getTimestamp();
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @param array $config
     *
     * @return \Cake\Chronos\Chronos|int
     */
    protected function getCookieExpirationDate(array $config)
    {
        return ($config['expire_on_close'] ?? false) ?
            0 :
            Chronos::now()->addSeconds($config['lifetime']);
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
