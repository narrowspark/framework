<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Middleware;

use Cake\Chronos\Chronos;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Session\Exceptions\TokenMismatchException;
use Viserio\Component\Cookie\SetCookie;
use Viserio\Component\Session\SessionManager;

class VerifyCsrfTokenMiddleware implements MiddlewareInterface
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
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Enviroment.
     *
     * @var string
     */
    protected $env;

    /**
     * Create a new session middleware.
     *
     * @param \Viserio\Component\Session\SessionManager $manager
     * @param string                                    $env
     */
    public function __construct(SessionManager $manager, string $env = 'production')
    {
        $this->manager      = $manager;
        $this->env          = $env;
        $this->driverConfig = $manager->getDriverConfig($manager->getDefaultDriver());
        $this->config       = $manager->getConfig();
    }

    /**
     * {@inheritdoc}
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
        return php_sapi_name() == 'cli' && $this->env === 'testing';
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
        $data         = $request->getParsedBody();
        $token        = $data['_token'] ?? $request->getHeaderLine('X-CSRF-TOKEN');

        if (! $token && $header = $request->getHeaderLine('X-XSRF-TOKEN')) {
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
    protected function addCookieToResponse(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $config = $this->config;

        $setCookie = new SetCookie(
            'XSRF-TOKEN',
            $request->getAttribute('session')->getToken(),
            $config['csrf.livetime'] ?? Chronos::now()->getTimestamp() + 60 * 120,
            $config['path'],
            $config['domain'],
            $config['secure'] ?? false,
            false,
            $config['csrf.samesite'] ?? false
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
        return in_array(mb_strtoupper($request->getMethod()), ['HEAD', 'GET', 'OPTIONS'], true);
    }
}
