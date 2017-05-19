<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Defuse\Crypto\Key;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\SessionManager;

class StartSessionMiddlewareTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Filesystem\Filesystem|null
     */
    private $files;

    public function setUp()
    {
        parent::setUp();

        $this->files = new Filesystem();

        $this->files->createDirectory(__DIR__ . '/stubs');
    }

    public function tearDown()
    {
        $this->files->deleteDirectory(__DIR__ . '/stubs');
        $this->files = null;

        parent::tearDown();
    }

    public function testAddSessionToResponse()
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
                    'cookie'          => 'test',
                    'path'            => '/',
                    'expire_on_close' => false,
                    'lottery'         => [2, 100],
                    'lifetime'        => 1440,
                    'domain'          => 'google.com',
                    'http_only'       => false,
                    'secure'          => false,
                ],
            ]);
        $manager = new SessionManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
                FilesystemContract::class => $this->files,
                EncrypterContract::class  => new Encrypter(Key::createNewRandomKey()->saveToAsciiSafeString()),
            ])
        );

        $middleware = new StartSessionMiddleware($manager);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request  = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        self::assertTrue(is_array($response->getHeader('Set-Cookie')));
    }

    public function testAddSessionToCookie()
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
                    'default' => 'cookie',
                    'drivers' => [
                        'cookie' => [],
                    ],
                    'cookie'          => 'test',
                    'expire_on_close' => false,
                    'lottery'         => [2, 100],
                    'lifetime'        => 1440,
                    'domain'          => '/',
                    'http_only'       => false,
                    'secure'          => false,
                ],
            ]);

        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once();

        $manager = new SessionManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
                FilesystemContract::class => $this->files,
                JarContract::class        => $jar,
                EncrypterContract::class  => new Encrypter(Key::createNewRandomKey()->saveToAsciiSafeString()),
            ])
        );

        $middleware = new StartSessionMiddleware($manager);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request  = (new ServerRequestFactory())->createServerRequestFromArray($server);

        $middleware->process($request, new DelegateMiddleware(function ($request) {
            self::assertInstanceOf(StoreContract::class, $request->getAttribute('session'));

            return (new ResponseFactory())->createResponse(200);
        }));
    }
}
