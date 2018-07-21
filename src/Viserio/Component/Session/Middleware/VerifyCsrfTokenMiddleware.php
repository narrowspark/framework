<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Middleware;

use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
     * Create a new session middleware.
     *
     * @param \Viserio\Component\Session\SessionManager $manager
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager      = $manager;
        $this->lifetime     = $manager->getConfig()['lifetime'];
        $this->cookieConfig = $manager->getConfig()['cookie'];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\Session\Exception\SessionNotStartedException
     * @throws \Viserio\Component\Contract\Session\Exception\TokenMismatchException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $request->getAttribute('session') instanceof StoreContract) {
            throw new SessionNotStartedException('The session is not started.');
        }

        $response = $handler->handle($request);

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
        return \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) && ($this->manager->getConfig()['env'] ?? 'prod') === 'testing';
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
        $token        = $request->getAttribute('_token') ?? $request->getHeaderLine('x-csrf-token');
        $header       = $request->getHeaderLine('x-xsrf-token');

        if ($token === '' && $header !== '') {
            try {
                $key          = KeyFactory::loadEncryptionKey($this->manager->getConfig()['key_path']);
                $hiddenString = Crypto::decrypt($header, $key);
                $token        = $hiddenString->getString();
            } catch (InvalidMessage $exception) {
                $token = $header;
            }
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
     * @throws \InvalidArgumentException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addCookieToResponse(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $uri = $request->getUri();

        $setCookie = new SetCookie(
            'XSRF-TOKEN',
            $request->getAttribute('session')->getToken(),
            $this->lifetime,
            $this->cookieConfig['path'],
            $this->cookieConfig['domain'] ?? $uri->getHost(),
            $this->cookieConfig['secure'] ?? ($uri->getScheme() === 'https'),
            false,
            $this->cookieConfig['samesite']
        );

        return $response->withAddedHeader('set-cookie', (string) $setCookie);
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
