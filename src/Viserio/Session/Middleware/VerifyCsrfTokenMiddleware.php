<?php
declare(strict_types=1);
namespace Viserio\Session\Middleware;

use Cake\Chronos\Chronos;
use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Session\Exception\TokenMismatchException;
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
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Create a new session middleware.
     *
     * @param \Viserio\Session\SessionManager $manager
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inhertidoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $response = $delegate->process($request);

        if ($this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->shouldPassThrough($request) ||
            $this->tokensMatch($request)
        ) {
            return $this->addCookieToResponse($request, $response);
        }

        throw new TokenMismatchException();
    }

    /**
     * Determine if the application is running unit tests.
     *
     * @return bool
     */
    protected function runningUnitTests(): bool
    {
        return php_sapi_name() == 'cli' && $this->config->get('app.env') == 'testing';
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    protected function shouldPassThrough(ServerRequestInterface $request): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->getUri()->getPath() === $except) {
                return true;
            }
        }

        return false;
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
        $sessionToken = $request->getAttribute('session')->getToken();
        $data = $request->getParsedBody();
        $token = $data['_token'] ?? $request->getHeader('X-CSRF-TOKEN');

        if (! $token && $header = $request->getHeader('X-XSRF-TOKEN')) {
            $token = $this->manager->getEncrypter()->decrypt($header);
        }

        if (! is_string($sessionToken) || ! is_string($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Add the CSRF token to the response cookies.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addCookieToResponse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $this->manager->getConfig();

        $setCookie = new Cookie(
            'XSRF-TOKEN',
            $request->getAttribute('session')->getToken(),
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
