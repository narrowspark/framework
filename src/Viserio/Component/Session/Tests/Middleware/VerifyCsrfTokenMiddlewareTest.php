<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Cake\Chronos\Chronos;
use Defuse\Crypto\Key;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\CallableMiddleware;
use Narrowspark\TestingHelper\Middleware\Dispatcher;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\Middleware\VerifyCsrfTokenMiddleware;
use Viserio\Component\Session\SessionManager;

class VerifyCsrfTokenMiddlewareTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Filesystem\Filesystem
     */
    private $files;

    /**
     * @var \Viserio\Component\Encryption\Encrypter|null
     */
    private $encrypter;

    public function setUp()
    {
        parent::setUp();

        $this->files = new Filesystem();
        $this->files->createDirectory(__DIR__ . '/stubs');

        $this->encrypter = new Encrypter(Key::createNewRandomKey()->saveToAsciiSafeString());
    }

    public function tearDown()
    {
        $this->files->deleteDirectory(__DIR__ . '/stubs');
        $this->files = $this->encrypter = null;

        parent::tearDown();
    }

    public function testSessionCsrfMiddlewareSetCookie()
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
                    'cookie'          => 'session',
                    'path'            => '/',
                    'expire_on_close' => false,
                    'lottery'         => [2, 100],
                    'lifetime'        => 1440,
                    'domain'          => 'test.com',
                    'http_only'       => false,
                    'secure'          => false,
                    'csrf'            => [
                        'samesite' => false,
                        'livetime' => Chronos::now()->getTimestamp() + 60 * 1200,
                    ],
                ],
            ]);
        $manager = $this->getSessionManager($config);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request = $request->withMethod('PUT');

        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new CallableMiddleware(function ($request, $delegate) {
                    $request = $request->withAttribute('_token', $request->getAttribute('session')->getToken());

                    return $delegate->process($request);
                }),
                new VerifyCsrfTokenMiddleware($manager),
                new CallableMiddleware(function ($request, $delegate) {
                    return (new ResponseFactory())->createResponse(200);
                }),
            ]
        );

        $response = $dispatcher->dispatch($request);

        self::assertTrue(is_array($response->getHeader('Set-Cookie')));
    }

    public function testSessionCsrfMiddlewareReadsXCSRFTOKEN()
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
                    'cookie'          => 'session',
                    'path'            => '/',
                    'expire_on_close' => false,
                    'lottery'         => [2, 100],
                    'lifetime'        => 1440,
                    'domain'          => 'test.com',
                    'http_only'       => false,
                    'secure'          => false,
                    'csrf'            => [
                        'samesite' => false,
                        'livetime' => Chronos::now()->getTimestamp() + 60 * 120,
                    ],
                ],
            ]);
        $manager = $this->getSessionManager($config);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request    = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request    = $request->withMethod('PUT');

        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new CallableMiddleware(function ($request, $delegate) {
                    $request = $request->withAddedHeader('X-CSRF-TOKEN', $request->getAttribute('session')->getToken());

                    return $delegate->process($request);
                }),
                new VerifyCsrfTokenMiddleware($manager),
                new CallableMiddleware(function ($request, $delegate) {
                    return (new ResponseFactory())->createResponse(200);
                }),
            ]
        );

        $response = $dispatcher->dispatch($request);

        self::assertTrue(is_array($response->getHeader('Set-Cookie')));
    }

    public function testSessionCsrfMiddlewareReadsXXSRFTOKEN()
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
                    'cookie'          => 'session',
                    'path'            => '/',
                    'expire_on_close' => false,
                    'lottery'         => [2, 100],
                    'lifetime'        => 1440,
                    'domain'          => 'test.com',
                    'http_only'       => false,
                    'secure'          => false,
                    'csrf'            => [
                        'samesite' => false,
                        'livetime' => Chronos::now()->getTimestamp() + 60 * 120,
                    ],
                ],
            ]);
        $manager = $this->getSessionManager($config);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request    = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request    = $request->withMethod('PUT');

        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new CallableMiddleware(function ($request, $delegate) {
                    $request = $request->withAddedHeader(
                        'X-XSRF-TOKEN',
                        $this->encrypter->encrypt($request->getAttribute('session')->getToken())
                    );

                    return $delegate->process($request);
                }),
                new VerifyCsrfTokenMiddleware($manager),
                new CallableMiddleware(function ($request, $delegate) {
                    return (new ResponseFactory())->createResponse(200);
                }),
            ]
        );

        $response = $dispatcher->dispatch($request);

        self::assertTrue(is_array($response->getHeader('Set-Cookie')));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Session\Exceptions\TokenMismatchException
     */
    public function testSessionCsrfMiddlewareToThrowException()
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
                    'cookie'          => 'session',
                    'path'            => '/',
                    'expire_on_close' => false,
                    'lottery'         => [2, 100],
                    'lifetime'        => 1440,
                    'domain'          => 'test.com',
                    'http_only'       => false,
                    'secure'          => false,
                    'csrf'            => [
                        'samesite' => false,
                        'livetime' => Chronos::now()->getTimestamp() + 60 * 120,
                    ],
                ],
            ]);
        $manager = $this->getSessionManager($config);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request    = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request    = $request->withMethod('PUT');

        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new VerifyCsrfTokenMiddleware($manager),
                new CallableMiddleware(function ($request, $delegate) {
                    return (new ResponseFactory())->createResponse(200);
                }),
            ]
        );

        $response = $dispatcher->dispatch($request);

        self::assertTrue(is_array($response->getHeader('Set-Cookie')));
    }

    private function getSessionManager($config)
    {
        return new SessionManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
                FilesystemContract::class => $this->files,
                EncrypterContract::class  => $this->encrypter,
            ])
        );
    }
}
