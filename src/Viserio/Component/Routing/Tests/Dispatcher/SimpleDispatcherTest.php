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

namespace Viserio\Component\Routing\Tests\Dispatcher;

use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatcher\SimpleDispatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 *
 * @small
 */
final class SimpleDispatcherTest extends AbstractDispatcherTest
{
    /** @var string */
    private $simpleDispatcherPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->simpleDispatcherPath = $this->patch . \DIRECTORY_SEPARATOR . 'SimpleDispatcherTest.cache';

        $dispatcher = new SimpleDispatcher();
        $dispatcher->setCachePath($this->simpleDispatcherPath);
        $dispatcher->refreshCache(true);

        $this->dispatcher = $dispatcher;
    }

    public function testHandleFound(): void
    {
        self::assertSame($this->simpleDispatcherPath, $this->dispatcher->getCachePath());

        $collection = new RouteCollection();
        $route = new Route(
            'GET',
            '/test',
            static function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody((new StreamFactory())->createStream('hello'));
            }
        );
        $route->setInvoker(new Invoker());

        $collection->add($route);

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test')
        );

        self::assertSame('hello', (string) $response->getBody());
        self::assertInstanceOf(Route::class, $this->dispatcher->getCurrentRoute());

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test/')
        );

        self::assertSame('hello', (string) $response->getBody());
        self::assertInstanceOf(Route::class, $this->dispatcher->getCurrentRoute());
    }
}
