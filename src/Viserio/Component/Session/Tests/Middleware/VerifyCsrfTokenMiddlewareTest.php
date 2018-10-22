<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Middleware\CallableMiddleware;
use Narrowspark\TestingHelper\Middleware\Dispatcher;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use Viserio\Component\Contract\Session\Exception\TokenMismatchException;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\Middleware\VerifyCsrfTokenMiddleware;
use Viserio\Component\Session\SessionManager;

/**
 * @internal
 */
final class VerifyCsrfTokenMiddlewareTest extends MockeryTestCase
{
    /**
     * @var string
     */
    private $keyPath;

    /**
     * @var \Viserio\Component\Session\SessionManager
     */
    private $sessionManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->keyPath = __DIR__ . \DIRECTORY_SEPARATOR . 'session_key';

        KeyFactory::save(KeyFactory::generateEncryptionKey(), $this->keyPath);

        $this->sessionManager = new SessionManager([
            'viserio' => [
                'session' => [
                    'default' => 'file',
                    'env'     => 'local',
                    'drivers' => [
                        'file' => [
                            'path' => __DIR__,
                        ],
                    ],
                    'key_path' => $this->keyPath,
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        \unlink($this->keyPath);
    }

    public function testSessionCsrfMiddlewareSetCookie(): void
    {
        $manager    = $this->sessionManager;
        $request    = new ServerRequest('/', 'POST');
        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new CallableMiddleware(function ($request, $handler) {
                    $request = $request->withAttribute('_token', $request->getAttribute('session')->getToken());

                    return $handler->handle($request);
                }),
                new VerifyCsrfTokenMiddleware($manager),
                new CallableMiddleware(function () {
                    return (new ResponseFactory())->createResponse();
                }),
            ]
        );

        $response = $dispatcher->dispatch($request);

        static::assertInternalType('array', $response->getHeader('set-cookie'));
    }

    public function testSessionCsrfMiddlewareReadsXCSRFTOKEN(): void
    {
        $manager    = $this->sessionManager;
        $request    = new ServerRequest('/', 'POST');
        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new CallableMiddleware(function ($request, $handler) {
                    $request = $request->withAddedHeader('x-csrf-token', $request->getAttribute('session')->getToken());

                    return $handler->handle($request);
                }),
                new VerifyCsrfTokenMiddleware($manager),
                new CallableMiddleware(function () {
                    return (new ResponseFactory())->createResponse();
                }),
            ]
        );

        $response = $dispatcher->dispatch($request);

        static::assertInternalType('array', $response->getHeader('set-cookie'));
    }

    public function testSessionCsrfMiddlewareReadsXXSRFTOKEN(): void
    {
        $manager    = $this->sessionManager;
        $request    = new ServerRequest('/', 'POST');
        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new CallableMiddleware(function ($request, $handler) {
                    $key = KeyFactory::loadEncryptionKey($this->keyPath);

                    $request = $request->withAddedHeader(
                        'x-xsrf-token',
                        Crypto::encrypt(
                            new HiddenString($request->getAttribute('session')->getToken()),
                            $key
                        )
                    );

                    return $handler->handle($request);
                }),
                new VerifyCsrfTokenMiddleware($manager),
                new CallableMiddleware(function () {
                    return (new ResponseFactory())->createResponse();
                }),
            ]
        );

        $response = $dispatcher->dispatch($request);

        static::assertInternalType('array', $response->getHeader('set-cookie'));
    }

    public function testSessionCsrfMiddlewareToThrowException(): void
    {
        $this->expectException(TokenMismatchException::class);

        $manager    = $this->sessionManager;
        $request    = new ServerRequest('/', 'POST');
        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new VerifyCsrfTokenMiddleware($manager),
                new CallableMiddleware(function () {
                    return (new ResponseFactory())->createResponse();
                }),
            ]
        );

        $response = $dispatcher->dispatch($request);

        static::assertInternalType('array', $response->getHeader('set-cookie'));
    }
}
