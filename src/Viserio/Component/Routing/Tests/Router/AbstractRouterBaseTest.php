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
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     * @param mixed $expectedResult
     * @param mixed $status
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

    /**
     * @param \Viserio\Contract\Routing\Router $router
     *
     * @return void
     */
    abstract protected function definitions(RouterContract $router): void;

    abstract protected static function provideRouterCases(): iterable;
}
