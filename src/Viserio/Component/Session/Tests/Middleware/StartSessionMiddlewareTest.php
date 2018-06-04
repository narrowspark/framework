<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\RequestHandlerMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\KeyFactory;
use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\SessionManager;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class StartSessionMiddlewareTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var string
     */
    private $keyPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->keyPath = self::normalizeDirectorySeparator(__DIR__ . '/session_key');

        KeyFactory::save(KeyFactory::generateEncryptionKey(), $this->keyPath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \unlink($this->keyPath);
    }

    public function testAddSessionToResponse(): void
    {
        $manager = $this->arrangeSessionManager();

        $middleware = new StartSessionMiddleware($manager);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';

        unset($server['PHP_SELF']);

        $request  = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $response = $middleware->process($request, new RequestHandlerMiddleware(function () {
            return (new ResponseFactory())->createResponse();
        }));

        $this->assertInternalType('array', $response->getHeader('set-cookie'));
    }

    public function testAddSessionToCookie(): void
    {
        $manager = $this->arrangeSessionManager('cookie');

        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once();

        $manager->setCookieJar($jar);

        $middleware = new StartSessionMiddleware($manager);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';

        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);

        $middleware->process($request, new RequestHandlerMiddleware(function ($request) {
            $this->assertInstanceOf(StoreContract::class, $request->getAttribute('session'));

            return (new ResponseFactory())->createResponse();
        }));
    }

    private function arrangeSessionManager(string $default = 'file')
    {
        return new SessionManager(
            new ArrayContainer([
                'config' => [
                    'viserio' => [
                        'session' => [
                            'default' => $default,
                            'drivers' => [
                                'file' => [
                                    'path' => __DIR__,
                                ],
                            ],
                            'key_path' => $this->keyPath,
                        ],
                    ],
                ],
            ])
        );
    }
}
