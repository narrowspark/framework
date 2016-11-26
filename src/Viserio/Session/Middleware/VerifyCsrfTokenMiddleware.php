<?php
declare(strict_types=1);
namespace Viserio\Session\Middleware;

use Cake\Chronos\Chronos;
use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Schnittstabil\Csrf\TokenService\TokenService;
use Viserio\Cookie\Cookie;
use Viserio\Session\SessionManager;

class VerifyCsrfTokenMiddleware implements ServerMiddlewareInterface
{
    /**
     * The session manager.
     *
     * @var \Viserio\Session\SessionManager
     */
    protected $manager;

    /**
     * TokenService for building.
     *
     * @var \Schnittstabil\Csrf\TokenService\TokenServiceInterface
     */
    protected $tokenService;

    /**
     * Create a new session middleware.
     *
     * @param \Viserio\Session\SessionManager $manager
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;

        $config = $manager->getConfig();

        $this->tokenService = new TokenService(
            $config->get('session.key'),
            $config->get('session.csrf.livetime', Chronos::now()->getTimestamp() + 60 * 120),
            $config->get('session.csrf.algo', 'SHA512')
        );
    }

    /**
     * {@inhertidoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $request = $this->generateNewToken($request);

        $response = $delegate->process($request);

        if ($this->isReading($request) || $this->tokensMatch($request)) {
            $response = $this->addCookieToResponse($response);
        }

        return $response;
    }

    /**
     * Generates a new CSRF token and attaches it to the Request Object
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function generateNewToken(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute('X-XSRF-TOKEN', $this->tokenService->generate());
    }

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    protected function tokensMatch(ServerRequestInterface $request): bool
    {
        return $this->tokenService->validate($request->getAttribute('X-XSRF-TOKEN'));
    }

    /**
     * Add the CSRF token to the response cookies.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addCookieToResponse(ResponseInterface $response): ResponseInterface
    {
        $config = $this->manager->getConfig();

        $setCookie = new Cookie(
            'XSRF-TOKEN',
            $this->tokenService->generate(),
            $config->get('session.csrf.livetime', Chronos::now()->getTimestamp() + 60 * 120),
            $config->get('session.path'),
            $config->get('session.domain'),
            $config->get('session.secure', false),
            false,
            $config->get('session.csrf.samesite', false)
        );

        return $response->withAddedHeader('Set-Cookie', (string) $setCookie);
    }

    /**
     * Determine if the HTTP request uses a ‘read’ verb.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isReading(ServerRequestInterface $request): bool
    {
        return in_array(strtoupper($request->getMethod()), ['HEAD', 'GET', 'OPTIONS'], true);
    }
}
