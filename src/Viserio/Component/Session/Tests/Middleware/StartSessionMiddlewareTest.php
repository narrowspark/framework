<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Session\Tests;

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

        $jar = \Mockery::mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once();

        $manager->setCookieJar($jar);

        $middleware = new StartSessionMiddleware($manager);

        $middleware->process(new ServerRequest('/', 'GET'), new RequestHandlerMiddleware(function ($request) {
            Assert::assertInstanceOf(StoreContract::class, $request->getAttribute('session'));

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
