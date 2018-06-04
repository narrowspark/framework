<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\CallableMiddleware;
use Narrowspark\TestingHelper\Middleware\Dispatcher;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\Middleware\VerifyCsrfTokenMiddleware;
use Viserio\Component\Session\SessionManager;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class VerifyCsrfTokenMiddlewareTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

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

        $this->keyPath = self::normalizeDirectorySeparator(__DIR__ . '/session_key');

        KeyFactory::save(KeyFactory::generateEncryptionKey(), $this->keyPath);

        $this->sessionManager = new SessionManager(
            new ArrayContainer([
                'config' => [
                    'viserio' => [
                        'session' => [
                            'default' => 'file',
                            'drivers' => [
                                'file' => [
                                    'path' => __DIR__,
                                ],
                            ],
                            'key_path' => $this->keyPath,
                        ],
                    ],
                ],
            ])
        );
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
        $manager = $this->sessionManager;

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request = $request->withMethod('POST');

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

        $this->assertInternalType('array', $response->getHeader('set-cookie'));
    }

    public function testSessionCsrfMiddlewareReadsXCSRFTOKEN(): void
    {
        $manager = $this->sessionManager;

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request = $request->withMethod('POST');

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

        $this->assertInternalType('array', $response->getHeader('set-cookie'));
    }

    public function testSessionCsrfMiddlewareReadsXXSRFTOKEN(): void
    {
        $manager = $this->sessionManager;

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request = $request->withMethod('POST');

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

        $this->assertInternalType('array', $response->getHeader('set-cookie'));
    }

    public function testSessionCsrfMiddlewareToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Session\Exception\TokenMismatchException::class);

        $manager = $this->sessionManager;

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';

        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request = $request->withMethod('POST');

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

        $this->assertInternalType('array', $response->getHeader('set-cookie'));
    }
}
