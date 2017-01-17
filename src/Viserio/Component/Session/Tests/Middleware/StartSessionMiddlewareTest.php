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

    /**
     * @var \Viserio\Component\Session\SessionManager
     */
    private $manager;

    public function setUp()
    {
        parent::setUp();

        $this->files = new Filesystem();

        $this->files->createDirectory(__DIR__ . '/stubs');

        $encrypter = new Encrypter(Key::createNewRandomKey());
        $config    = $this->mock(RepositoryContract::class);

        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->andReturn(true);

        $manager = new SessionManager($config, $encrypter);
        $manager->setContainer(new ArrayContainer([
            FilesystemContract::class => $this->files,
            JarContract::class        => $jar,
        ]));

        $this->manager = $manager;
    }

    public function tearDown()
    {
        $this->files->deleteDirectory(__DIR__ . '/stubs');
        $this->files = $this->manager = null;

        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testAddSessionToResponse()
    {
        $manager = $this->manager;
        $config  = $manager->getConfig();

        $config->shouldReceive('get')
            ->once()
            ->with('session.drivers', []);
        $config->shouldReceive('get')
            ->with('session.driver', 'local')
            ->once()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.driver', null)
            ->twice()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.lifetime')
            ->twice()
            ->andReturn(5);
        $config->shouldReceive('get')
            ->with('session.cookie', '')
            ->once()
            ->andReturn('test');
        $config->shouldReceive('get')
            ->with('session.path')
            ->twice()
            ->andReturn(__DIR__ . '/stubs');
        $config->shouldReceive('get')
            ->with('session.expire_on_close', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.lottery')
            ->once()
            ->andReturn([2, 100]);
        $config->shouldReceive('get')
            ->with('session.lifetime', 1440)
            ->andReturn(1440);
        $config->shouldReceive('get')
            ->with('session.domain')
            ->once()
            ->andReturn('/');
        $config->shouldReceive('get')
            ->with('session.secure', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.http_only', false)
            ->once()
            ->andReturn(false);

        $middleware = new StartSessionMiddleware($manager);
        $request    = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        self::assertTrue(is_array($response->getHeader('Set-Cookie')));
    }

    public function testAddSessionToCookie()
    {
        $manager = $this->manager;
        $config  = $manager->getConfig();

        $config->shouldReceive('get')
            ->once()
            ->with('session.drivers', []);
        $config->shouldReceive('get')
            ->with('session.driver', 'local')
            ->once()
            ->andReturn('cookie');
        $config->shouldReceive('get')
            ->with('session.driver', null)
            ->twice()
            ->andReturn('cookie');
        $config->shouldReceive('get')
            ->with('session.lifetime')
            ->once()
            ->andReturn(5);
        $config->shouldReceive('get')
            ->with('session.cookie', '')
            ->once()
            ->andReturn('test');
        $config->shouldReceive('get')
            ->with('session.lottery')
            ->once()
            ->andReturn([2, 100]);
        $config->shouldReceive('get')
            ->with('session.lifetime', 1440)
            ->andReturn(1440);

        $middleware = new StartSessionMiddleware($manager);
        $request    = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            self::assertInstanceOf(StoreContract::class, $request->getAttribute('session'));

            return (new ResponseFactory())->createResponse(200);
        }));
    }
}
