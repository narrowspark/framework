<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Middleware;

use Cake\Chronos\Chronos;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
     * Cookie config from session manager.
     *
     * @var array
     */
    protected $cookieConfig = [];

    /**
     * Session cookie lifetime.
     *
     * @var int
     */
    protected $lifetime;

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
        $this->lifetime     = $manager->getConfig()['lifetime'];
        $this->cookieConfig = $manager->getConfig()['cookie'];
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // We need to start the session so that the data is ready.
        // Note that the Narrowspark sessions do not make use of PHP
        // "native" sessions in any way since they are crappy.
        $session = $this->startSession($request);

        $request = $request->withAttribute('session', $session);

        $this->collectGarbage($session);

        $response = $handler->handle($request);

        // Again, if the session has been configured we will need to close out the session
        // so that the attributes may be persisted to some storage medium. We will also
        // add the session identifier cookie to the application response headers now.
        $session = $this->storeCurrentUrl($request, $session);

        return $this->addCookieToResponse($request, $response, $session);
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
        $session   = $this->manager->getDriver();
        $cookies   = RequestCookies::fromRequest($request);

        $session->setId($cookies->has($session->getName()) ? $cookies->get($session->getName())->getValue() : '');

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
            $request->getHeaderLine('x-requested-with') !== 'XMLHttpRequest'
        ) {
            $session->setPreviousUrl((string) $request->getUri());
        }

        return $session;
    }

    /**
     * Remove the garbage from the session if necessary.
     *
     * @param \Viserio\Component\Contract\Session\Store $session
     *
     * @return void
     */
    protected function collectGarbage(StoreContract $session): void
    {
        $lottery     = $this->cookieConfig['lottery'];
        $hitsLottery = \random_int(1, $lottery[1]) <= $lottery[0];

        // Here we will see if this request hits the garbage collection lottery by hitting
        // the odds needed to perform garbage collection on any given request. If we do
        // hit it, we'll call this handler to let it delete all the expired sessions.
        if ($hitsLottery) {
            $session->getHandler()->gc($this->lifetime);
        }
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param \Viserio\Component\Contract\Session\Store $session
     *
     * @throws \Viserio\Component\Contract\Cookie\Exception\InvalidArgumentException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addCookieToResponse(ServerRequestInterface $request, ResponseInterface $response, StoreContract $session): ResponseInterface
    {
        if ($session->getHandler() instanceof CookieSessionHandler) {
            $session->save();
        }

        $uri    = $request->getUri();

        $setCookie = new SetCookie(
            $session->getName(),
            $session->getId(),
            $this->cookieConfig['expire_on_close'] === true ? 0 : Chronos::now()->addSeconds($this->lifetime),
            $this->cookieConfig['path'] ?? '/',
            $this->cookieConfig['domain'] ?? $uri->getHost(),
            $this->cookieConfig['secure'] ?? ($uri->getScheme() === 'https'),
            $this->cookieConfig['http_only'] ?? true,
            $this->cookieConfig['samesite'] ?? false
        );

        return $response->withAddedHeader('set-cookie', (string) $setCookie);
    }
}
