<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Middleware;

use Cake\Chronos\Chronos;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Session\Exception\SessionNotStartedException;
use Viserio\Component\Contract\Session\Exception\TokenMismatchException;
use Viserio\Component\Contract\Session\Store as StoreContract;
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
     * Session manager driver config.
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
        if (! $request->getAttribute('session') instanceof StoreContract) {
            throw new SessionNotStartedException('The session is not started.');
        }

        $response = $delegate->process($request);

        if ($this->isReading($request) ||
            $this->runningUnitTests() ||
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
        return PHP_SAPI == 'cli' && ($this->config['env'] ?? 'production') === 'testing';
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
        $token        = $request->getAttribute('_token') ?? $request->getHeaderLine('X-CSRF-TOKEN');

        if (! $token && $header = $request->getHeaderLine('X-XSRF-TOKEN')) {
            $hiddenString = $this->manager->getEncrypter()->decrypt($header);
            $token        = $hiddenString->getString();
        }

        if (! \is_string($sessionToken) || ! \is_string($token)) {
            return false;
        }

        return \hash_equals($sessionToken, $token);
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
        $uri    = $request->getUri();

        $setCookie = new SetCookie(
            'XSRF-TOKEN',
            $request->getAttribute('session')->getToken(),
            Chronos::now()->addSeconds($config['lifetime']),
            $config['path'],
            $config['domain'] ?? $uri->getHost(),
            $config['secure'] ?? ($uri->getScheme() === 'https'),
            false,
            $config['samesite']
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
        return \in_array(\mb_strtoupper($request->getMethod()), ['HEAD', 'GET', 'OPTIONS'], true);
    }
}
