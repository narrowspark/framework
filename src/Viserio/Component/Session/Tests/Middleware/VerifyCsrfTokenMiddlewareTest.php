<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Cake\Chronos\Chronos;
use Defuse\Crypto\Key;
use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\CallableMiddleware;
use Narrowspark\TestingHelper\Middleware\Dispatcher;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\Middleware\VerifyCsrfTokenMiddleware;
use Viserio\Component\Session\SessionManager;

class VerifyCsrfTokenMiddlewareTest extends TestCase
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

    /**
     * @var \Viserio\Component\Encryption\Encrypter
     */
    private $encrypter;

    public function setUp()
    {
        parent::setUp();

        $this->files = new Filesystem();

        $this->files->createDirectory(__DIR__ . '/stubs');

        $this->encrypter = new Encrypter(Key::createNewRandomKey());
        $config          = $this->mock(RepositoryContract::class);

        $manager = new SessionManager($config, $this->encrypter);
        $manager->setContainer(new ArrayContainer([
            FilesystemContract::class => $this->files,
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

    public function testSessionCsrfMiddlewareSetCookie()
    {
        $manager = $this->manager;
        $config  = $manager->getConfig();

        $config->shouldReceive('get')
            ->once()
            ->with('session.drivers', []);
        $config->shouldReceive('get')
            ->with('app.env')
            ->andReturn('dev');
        $config->shouldReceive('get')
            ->with('session.driver', null)
            ->twice()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.driver', 'local')
            ->once()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.path')
            ->times(3)
            ->andReturn(__DIR__ . '/stubs');
        $config->shouldReceive('get')
            ->with('session.csrf.samesite', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.csrf.livetime', $time = Chronos::now()->getTimestamp() + 60 * 120)
            ->once()
            ->andReturn($time);
        $config->shouldReceive('get')
            ->with('session.cookie', '')
            ->once()
            ->andReturn('session');
        $config->shouldReceive('get')
            ->with('session.expire_on_close', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.lottery')
            ->once()
            ->andReturn([2, 100]);
        $config->shouldReceive('get')
            ->with('session.lifetime')
            ->twice()
            ->andReturn(5);
        $config->shouldReceive('get')
            ->with('session.http_only', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.domain')
            ->twice()
            ->andReturn('/');
        $config->shouldReceive('get')
            ->with('session.secure', false)
            ->twice()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.lifetime', 1440)
            ->andReturn(1440);

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);
        $request = $request->withMethod('PUT');

        $dispatcher = new Dispatcher(
            [
                new StartSessionMiddleware($manager),
                new CallableMiddleware(function ($request, $delegate) {
                    $request = $request->withParsedBody(['_token' => $request->getAttribute('session')->getToken()]);

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
        $manager = $this->manager;
        $config  = $manager->getConfig();

        $config->shouldReceive('get')
            ->once()
            ->with('session.drivers', []);
        $config->shouldReceive('get')
            ->with('app.env')
            ->andReturn('dev');
        $config->shouldReceive('get')
            ->with('session.driver', null)
            ->twice()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.driver', 'local')
            ->once()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.path')
            ->times(3)
            ->andReturn(__DIR__ . '/stubs');
        $config->shouldReceive('get')
            ->with('session.csrf.samesite', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.csrf.livetime', $time = Chronos::now()->getTimestamp() + 60 * 120)
            ->once()
            ->andReturn($time);
        $config->shouldReceive('get')
            ->with('session.cookie', '')
            ->once()
            ->andReturn('session');
        $config->shouldReceive('get')
            ->with('session.expire_on_close', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.lottery')
            ->once()
            ->andReturn([2, 100]);
        $config->shouldReceive('get')
            ->with('session.lifetime')
            ->twice()
            ->andReturn(5);
        $config->shouldReceive('get')
            ->with('session.http_only', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.domain')
            ->twice()
            ->andReturn('/');
        $config->shouldReceive('get')
            ->with('session.secure', false)
            ->twice()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.lifetime', 1440)
            ->andReturn(1440);

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);
        $request = $request->withMethod('PUT');

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
        $manager = $this->manager;
        $config  = $manager->getConfig();

        $config->shouldReceive('get')
            ->once()
            ->with('session.drivers', []);
        $config->shouldReceive('get')
            ->with('app.env')
            ->andReturn('dev');
        $config->shouldReceive('get')
            ->with('session.driver', null)
            ->twice()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.driver', 'local')
            ->once()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.path')
            ->times(3)
            ->andReturn(__DIR__ . '/stubs');
        $config->shouldReceive('get')
            ->with('session.csrf.samesite', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.csrf.livetime', $time = Chronos::now()->getTimestamp() + 60 * 120)
            ->once()
            ->andReturn($time);
        $config->shouldReceive('get')
            ->with('session.cookie', '')
            ->once()
            ->andReturn('session');
        $config->shouldReceive('get')
            ->with('session.expire_on_close', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.lottery')
            ->once()
            ->andReturn([2, 100]);
        $config->shouldReceive('get')
            ->with('session.lifetime')
            ->twice()
            ->andReturn(5);
        $config->shouldReceive('get')
            ->with('session.http_only', false)
            ->once()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.domain')
            ->twice()
            ->andReturn('/');
        $config->shouldReceive('get')
            ->with('session.secure', false)
            ->twice()
            ->andReturn(false);
        $config->shouldReceive('get')
            ->with('session.lifetime', 1440)
            ->andReturn(1440);

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);
        $request = $request->withMethod('PUT');

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
     * @expectedException \Viserio\Component\Contracts\Session\Exception\TokenMismatchException
     */
    public function testSessionCsrfMiddlewareToThrowException()
    {
        $manager = $this->manager;
        $config  = $manager->getConfig();

        $config->shouldReceive('get')
            ->once()
            ->with('session.drivers', []);
        $config->shouldReceive('get')
            ->with('app.env')
            ->andReturn('dev');
        $config->shouldReceive('get')
            ->with('session.driver', null)
            ->once()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.driver', 'local')
            ->once()
            ->andReturn('local');
        $config->shouldReceive('get')
            ->with('session.path')
            ->once()
            ->andReturn(__DIR__ . '/stubs');
        $config->shouldReceive('get')
            ->with('session.lifetime')
            ->once()
            ->andReturn(1440);
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

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);
        $request = $request->withMethod('PUT');

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
}
