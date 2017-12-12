<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\SessionManager;

class StartSessionMiddlewareTest extends MockeryTestCase
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

    public function testAddSessionToResponse(): void
    {
        $manager = $this->getSessionManager();

        $middleware = new StartSessionMiddleware($manager);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';

        unset($server['PHP_SELF']);

        $request  = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $response = $middleware->process($request, new DelegateMiddleware(function () {
            return (new ResponseFactory())->createResponse();
        }));

        self::assertTrue(\is_array($response->getHeader('set-cookie')));
    }

    public function testAddSessionToCookie(): void
    {
        $manager = $this->getSessionManager('cookie');

        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once();

        $manager->setCookieJar($jar);

        $middleware = new StartSessionMiddleware($manager);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';

        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);

        $middleware->process($request, new DelegateMiddleware(function ($request) {
            self::assertInstanceOf(StoreContract::class, $request->getAttribute('session'));

            return (new ResponseFactory())->createResponse();
        }));
    }

    private function getSessionManager(string $default = 'file')
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
                    'default' => $default,
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
