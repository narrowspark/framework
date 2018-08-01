<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Middleware\RequestHandlerMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\KeyFactory;
use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\HttpFactory\ResponseFactory;
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

        $response = $middleware->process(new ServerRequest('/', 'GET'), new RequestHandlerMiddleware(function () {
            return (new ResponseFactory())->createResponse();
        }));

        static::assertInternalType('array', $response->getHeader('set-cookie'));
    }

    public function testAddSessionToCookie(): void
    {
        $manager = $this->arrangeSessionManager('cookie');

        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once();

        $manager->setCookieJar($jar);

        $middleware = new StartSessionMiddleware($manager);

        $middleware->process(new ServerRequest('/', 'GET'), new RequestHandlerMiddleware(function ($request) {
            static::assertInstanceOf(StoreContract::class, $request->getAttribute('session'));

            return (new ResponseFactory())->createResponse();
        }));
    }

    /**
     * @param string $default
     *
     * @return \Viserio\Component\Session\SessionManager
     */
    private function arrangeSessionManager(string $default = 'file'): SessionManager
    {
        return new SessionManager([
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
        ]);
    }
}
