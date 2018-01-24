<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\CallableMiddleware;
use Narrowspark\TestingHelper\Middleware\Dispatcher;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\HiddenString;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\Middleware\VerifyCsrfTokenMiddleware;
use Viserio\Component\Session\SessionManager;

class VerifyCsrfTokenMiddlewareTest extends MockeryTestCase
{
    /**
     * @var string
     */
    private $keyPath;

    public function setUp(): void
    {
        parent::setUp();

        $dir = __DIR__ . '/stubs';

        \mkdir($dir);

        $key = KeyFactory::generateKey();

        KeyFactory::saveKeyToFile($dir . '/session_key', $key);

        $this->keyPath = $dir . '/session_key';
    }

    public function tearDown(): void
    {
        \unlink($this->keyPath);
        \rmdir(__DIR__ . '/stubs');

        parent::tearDown();
    }

    public function testSessionCsrfMiddlewareSetCookie(): void
    {
        $manager = $this->getSessionManager();

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

        self::assertTrue(\is_array($response->getHeader('set-cookie')));
    }

    public function testSessionCsrfMiddlewareReadsXCSRFTOKEN(): void
    {
        $manager = $this->getSessionManager();

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

        self::assertTrue(\is_array($response->getHeader('set-cookie')));
    }

    public function testSessionCsrfMiddlewareReadsXXSRFTOKEN(): void
    {
        $manager = $this->getSessionManager();

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request = $request->withMethod('POST');

        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new CallableMiddleware(function ($request, $handler) {
                    $request = $request->withAddedHeader(
                        'x-xsrf-token',
                        (new Encrypter(KeyFactory::loadKey($this->keyPath)))->encrypt(new HiddenString($request->getAttribute('session')->getToken()))
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

        self::assertTrue(\is_array($response->getHeader('set-cookie')));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Session\Exception\TokenMismatchException
     */
    public function testSessionCsrfMiddlewareToThrowException(): void
    {
        $manager = $this->getSessionManager();

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

        self::assertTrue(\is_array($response->getHeader('set-cookie')));
    }

    private function getSessionManager()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'session' => [
                    'default' => 'file',
                    'drivers' => [
                        'file' => [
                            'path' => __DIR__ . '/stubs',
                        ],
                    ],
                    'key_path' => $this->keyPath,
                ],
            ]);

        return new SessionManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );
    }
}
