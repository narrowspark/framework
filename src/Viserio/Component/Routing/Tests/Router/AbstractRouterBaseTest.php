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

namespace Viserio\Component\Routing\Tests\Router;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Viserio\Component\Events\EventManager;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Router;
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 */
abstract class AbstractRouterBaseTest extends MockeryTestCase
{
    /** @var \Viserio\Contract\Routing\Router */
    protected $router;

    /** @var \Mockery\MockInterface|\Psr\Container\ContainerInterface */
    protected $containerMock;

    /** @var \Viserio\Component\HttpFactory\ResponseFactory */
    protected $responseFactory;

    /** @var \Viserio\Component\HttpFactory\ServerRequestFactory */
    protected $serverRequestFactory;

    /** @var \Viserio\Component\HttpFactory\StreamFactory */
    protected $streamFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $name = (new ReflectionClass($this))->getShortName();

        $dispatcher = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath(__DIR__ . \DIRECTORY_SEPARATOR . 'Cache' . \DIRECTORY_SEPARATOR . $name . '.cache');
        $dispatcher->refreshCache(true);
        $dispatcher->setEventManager(new EventManager());

        $this->containerMock = Mockery::mock(ContainerInterface::class);

        $router = new Router($dispatcher);
        $router->setContainer($this->containerMock);

        $this->router = $router;
        $this->responseFactory = new ResponseFactory();
        $this->serverRequestFactory = new ServerRequestFactory();
        $this->streamFactory = new StreamFactory();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $dir = __DIR__ . \DIRECTORY_SEPARATOR . 'Cache' . \DIRECTORY_SEPARATOR;

        \array_map(static function ($value): void {
            @\unlink($value);
        }, \glob($dir . \DIRECTORY_SEPARATOR . '*', \GLOB_NOSORT));

        @\rmdir($dir);
    }

    /**
     * @dataProvider provideRouterCases
     */
    public function testRouter($httpMethod, $uri, $expectedResult, $status = 200): void
    {
        $this->definitions($this->router);

        $actualResult = $this->router->dispatch(
            $this->serverRequestFactory->createServerRequest($httpMethod, $uri)
        );

        self::assertEquals($expectedResult, (string) $actualResult->getBody());
        self::assertSame($status, $actualResult->getStatusCode());
    }

    abstract protected function definitions(RouterContract $router): void;

    abstract protected static function provideRouterCases(): iterable;
}
