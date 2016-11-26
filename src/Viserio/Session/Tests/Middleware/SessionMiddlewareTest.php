<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Viserio\Contracts\Config\Manager as ConfigManagerContract;
use Viserio\Encryption\Encrypter;
use Viserio\Session\SessionManager;
use Viserio\Session\Middleware\SessionMiddleware;
use Viserio\HttpFactory\ServerRequestFactory;
use Cake\Chronos\Chronos;
use Viserio\HttpFactory\ResponseFactory;
use org\bovigo\vfs\vfsStream;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;

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

    public function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup();
        $this->files = new Filesystem();

        $this->files->createDirectory(__DIR__ . '/stubs');
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->files->deleteDirectory(__DIR__ . '/stubs');
    }

    public function testAddSessionToResponse()
    {
        $key = Key::createNewRandomKey();

        $encrypter = new Encrypter($key);
        $config = $this->mock(ConfigManagerContract::class);
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
            ->andReturn($key);
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

        $manager = new SessionManager($config, $encrypter);
        $manager->setContainer(new ArrayContainer([
            FilesystemContract::class => $this->files
        ]));

        $middleware = new SessionMiddleware($manager);
        $request = (new ServerRequestFactory)->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory)->createResponse(200);
        }));

        $this->assertTrue(isset($response->getHeaders()['Set-Cookie']));
    }

    public function testAddSessionToCookie()
    {
        $key = Key::createNewRandomKey();

        $encrypter = new Encrypter($key);
        $config = $this->mock(ConfigManagerContract::class);
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
            ->andReturn($key);
        $config->shouldReceive('get')
            ->with('session.lottery')
            ->once()
            ->andReturn([2, 100]);
        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once()
            ->andReturn(true);

        $manager = new SessionManager($config, $encrypter);
        $manager->setContainer(new ArrayContainer([
            JarContract::class => $jar
        ]));

        $middleware = new SessionMiddleware($manager);
        $request = (new ServerRequestFactory)->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory)->createResponse(200);
        }));
    }
}
