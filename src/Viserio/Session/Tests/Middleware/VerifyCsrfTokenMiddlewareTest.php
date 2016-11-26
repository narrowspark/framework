<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Cake\Chronos\Chronos;
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

    public function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup();
        $this->files = new Filesystem();

        $this->files->createDirectory(__DIR__ . '/stubs');
    }

    public function tearDown()
    {
        $this->files->deleteDirectory(__DIR__ . '/stubs');

        parent::tearDown();
    }

    public function testSessionCsrfMiddleware()
    {
        $key = Key::createNewRandomKey();

        $encrypter = new Encrypter($key);
        $config = $this->mock(ConfigManagerContract::class);
        $config->shouldReceive('get')
            ->with('session.drivers', []);
        $config->shouldReceive('get')
            ->with('session.key')
            ->once()
            ->andReturn($key->saveToAsciiSafeString());
        $config->shouldReceive('get')
            ->with('session.csrf.livetime', $time = Chronos::now()->getTimestamp() + 60 * 120)
            ->andReturn($time);
        $config->shouldReceive('get')
            ->with('session.csrf.algo', 'SHA512')
            ->once()
            ->andReturn('SHA512');
        $config->shouldReceive('get')
            ->with('session.path')
            ->twice()
            ->andReturn($this->root->url());
        $config->shouldReceive('get')
            ->with('session.domain')
            ->twice()
            ->andReturn('/');
        $config->shouldReceive('get')
            ->with('session.secure', false)
            ->twice()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.csrf.samesite', false)
            ->twice()
            ->andReturn(false);

        $manager = new SessionManager($config, $encrypter);
        $manager->setContainer(new ArrayContainer([
            FilesystemContract::class => $this->files,
        ]));

        $middleware = new VerifyCsrfTokenMiddleware($manager);
        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        $this->assertTrue(isset($response->getHeaders()['Set-Cookie']));

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);
        $request = $request->withMethod('PUT');

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            $this->assertTrue($request->getAttribute('X-XSRF-TOKEN') !== null);

            return (new ResponseFactory())->createResponse(200);
        }));
    }
}
