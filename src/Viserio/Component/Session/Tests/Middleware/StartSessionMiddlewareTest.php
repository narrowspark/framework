<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Defuse\Crypto\Key;
use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\SessionManager;

class StartSessionMiddlewareTest extends TestCase
{
    use MockeryTrait;

    /**
     * @var \Viserio\Component\Filesystem\Filesystem
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

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
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
                    'drivers' => [
                        'local' => []
                    ],
                    'cookie' => 'test',
                    'path' => __DIR__ . '/stubs',
                    'expire_on_close' => false,
                    'lottery' => [2, 100],
                    'lifetime' => 1440,
                    'domain' => '/',
                    'http_only' => false,
                    'secure' => false,
                ],
            ]);
        $manager = $this->getSessionManager($config);

        $middleware = new StartSessionMiddleware($manager);
        $request    = (new ServerRequestFactory())->createServerRequest($_SERVER);

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
                    'drivers' => [
                        'cookie' => []
                    ],
                    'cookie' => 'test',
                    'path' => __DIR__ . '/stubs',
                    'expire_on_close' => false,
                    'lottery' => [2, 100],
                    'lifetime' => 1440,
                    'domain' => '/',
                    'http_only' => false,
                    'secure' => false,
                ],
            ]);
        $manager = $this->getSessionManager($config);

        $middleware = new StartSessionMiddleware($manager);
        $request    = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            self::assertInstanceOf(StoreContract::class, $request->getAttribute('session'));

            return (new ResponseFactory())->createResponse(200);
        }));
    }

    private function getSessionManager($config)
    {
        return new SessionManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
                FilesystemContract::class => $this->files,
                JarContract::class        => $this->mock(JarContract::class),
            ]),
            new Encrypter(Key::createNewRandomKey())
        );
    }
}
