<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use org\bovigo\vfs\vfsStream;
use Viserio\Contracts\Config\Manager as ConfigManagerContract;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Encryption\Encrypter;
use Viserio\Filesystem\Filesystem;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;
use Viserio\Session\Middleware\SessionMiddleware;
use Viserio\Session\SessionManager;

class SessionMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var string
     */
    private $root;

    /**
     * @var \Viserio\Filesystem\Filesystem
     */
    private $files;

    private $key;

    private $manager;

    public function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup();
        $this->files = new Filesystem();

        $this->files->createDirectory(__DIR__ . '/stubs');

        $this->key = Key::createNewRandomKey();

        $encrypter = new Encrypter($this->key);
        $config = $this->mock(ConfigManagerContract::class);

        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->andReturn(true);

        $manager = new SessionManager($config, $encrypter);
        $manager->setContainer(new ArrayContainer([
            FilesystemContract::class => $this->files,
            JarContract::class => $jar,
        ]));

        $this->manager = $manager;
    }

    public function tearDown()
    {
        $this->files->deleteDirectory(__DIR__ . '/stubs');
        $this->files = $this->key = $this->manager = null;

        parent::tearDown();
    }

    public function testAddSessionToResponse()
    {
        $manager = $this->manager;
        $config = $manager->getConfig();

        $config->shouldReceive('get')
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
            ->andReturn(5);
        $config->shouldReceive('get')
            ->with('session.cookie', '')
            ->once()
            ->andReturn('test');
        $config->shouldReceive('get')
            ->with('session.path')
            ->twice()
            ->andReturn($this->root->url());
        $config->shouldReceive('get')
            ->with('session.expire_on_close', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.key')
            ->once()
            ->andReturn($this->key->saveToAsciiSafeString());
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

        $middleware = new SessionMiddleware($manager);
        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        $this->assertTrue(is_array($response->getHeader('Set-Cookie')));
    }

    public function testAddSessionToCookie()
    {
        $manager = $this->manager;
        $config = $manager->getConfig();

        $config->shouldReceive('get')
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
            ->andReturn(5);
        $config->shouldReceive('get')
            ->with('session.cookie', '')
            ->once()
            ->andReturn('test');
        $config->shouldReceive('get')
            ->with('session.key')
            ->once()
            ->andReturn($this->key->saveToAsciiSafeString());
        $config->shouldReceive('get')
            ->with('session.lottery')
            ->once()
            ->andReturn([2, 100]);

        $middleware = new SessionMiddleware($manager);
        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));
    }
}
