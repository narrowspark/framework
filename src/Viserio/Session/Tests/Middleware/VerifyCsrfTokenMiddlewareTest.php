<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Cake\Chronos\Chronos;
use Defuse\Crypto\Key;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\CallableMiddleware;
use Narrowspark\TestingHelper\Middleware\Dispatcher;
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
use Viserio\Session\Middleware\VerifyCsrfTokenMiddleware;
use Viserio\Session\SessionManager;

class VerifyCsrfTokenMiddlewareTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @var \Defuse\Crypto\Key
     */
    private $key;

    /**
     * @var \Viserio\Session\SessionManager
     */
    private $manager;

    public function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup();
        $this->files = new Filesystem();

        $this->files->createDirectory(__DIR__ . '/stubs');

        $encrypter = new Encrypter(Key::createNewRandomKey());
        $config = $this->mock(ConfigManagerContract::class);

        $manager = new SessionManager($config, $encrypter);
        $manager->setContainer(new ArrayContainer([
            FilesystemContract::class => $this->files,
        ]));

        $this->manager = $manager;
    }

    public function tearDown()
    {
        $this->files->deleteDirectory(__DIR__ . '/stubs');
        $this->files = $this->key = $this->manager = null;

        parent::tearDown();
    }

    public function testSessionCsrfMiddlewareSetCookie()
    {
        $manager = $this->manager;
        $config = $manager->getConfig();

        $config->shouldReceive('get')
            ->with('session.drivers', []);
        $config->shouldReceive('get')
            ->with('session.driver', null);
        $config->shouldReceive('get')
            ->with('session.path')
            ->andReturn($this->root->url());
        $config->shouldReceive('get')
            ->with('session.domain')
            ->andReturn('/');
        $config->shouldReceive('get')
            ->with('session.secure', false)
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.csrf.samesite', false)
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.lifetime', 1440)
            ->andReturn(1440);

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $dispatcher = new Dispatcher(
            [
                new SessionMiddleware($manager),
                new VerifyCsrfTokenMiddleware($manager),
                new CallableMiddleware(function ($request, $delegate) {
                    return (new ResponseFactory())->createResponse(200);
                }),
            ]
        );

        $response = $dispatcher->dispatch($request);

        $this->assertTrue(is_array($response->getHeader('Set-Cookie')));
    }

    // public function testSessionCsrfMiddlewareReadsXXsrfToken()
    // {
    //     $manager = $this->manager;
    //     $config = $manager->getConfig();

    //     $config->shouldReceive('get')
    //         ->with('session.drivers', []);
    //     $config->shouldReceive('get')
    //         ->with('session.path')
    //         ->andReturn($this->root->url());
    //     $config->shouldReceive('get')
    //         ->with('session.domain')
    //         ->andReturn('/');
    //     $config->shouldReceive('get')
    //         ->with('session.secure', false)
    //         ->andReturn(false);
    //     $config->shouldReceive('get')
    //         ->with('session.csrf.samesite', false)
    //         ->andReturn(false);

    //     $middleware = new VerifyCsrfTokenMiddleware($manager);

    //     $request = (new ServerRequestFactory())->createServerRequest($_SERVER);
    //     $request = $request->withMethod('PUT');

    //     $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
    //         $this->assertTrue($request->getAttribute('X-XSRF-TOKEN') !== null);

    //         return (new ResponseFactory())->createResponse(200);
    //     }));
    // }
}
