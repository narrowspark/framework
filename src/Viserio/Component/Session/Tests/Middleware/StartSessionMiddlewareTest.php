<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Session\Tests;

use Mockery;
use Narrowspark\TestingHelper\Middleware\RequestHandlerMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\KeyFactory;
use PHPUnit\Framework\Assert;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\Session\Middleware\StartSessionMiddleware;
use Viserio\Component\Session\SessionManager;
use Viserio\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Contract\Session\Store as StoreContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class StartSessionMiddlewareTest extends MockeryTestCase
{
    /** @var string */
    private $keyPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->keyPath = __DIR__ . \DIRECTORY_SEPARATOR . 'session_key';

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

        $response = $middleware->process(new ServerRequest('/', 'GET'), new RequestHandlerMiddleware(static function () {
            return (new ResponseFactory())->createResponse();
        }));

        self::assertIsArray($response->getHeader('set-cookie'));
    }

    public function testAddSessionToCookie(): void
    {
        $manager = $this->arrangeSessionManager('cookie');

        $jar = Mockery::mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once();

        $manager->setCookieJar($jar);

        $middleware = new StartSessionMiddleware($manager);

        $middleware->process(new ServerRequest('/', 'GET'), new RequestHandlerMiddleware(function ($request) {
            Assert::assertInstanceOf(StoreContract::class, $request->getAttribute('session'));

            return (new ResponseFactory())->createResponse();
        }));
    }

    private function arrangeSessionManager(string $default = 'file'): SessionManager
    {
        return new SessionManager([
            'viserio' => [
                'session' => [
                    'default' => $default,
                    'env' => 'local',
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
