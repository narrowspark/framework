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

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 */
abstract class AbstractDispatcherTest extends MockeryTestCase
{
    /** @var \Viserio\Contract\Routing\Dispatcher */
    protected $dispatcher;

    /** @var string */
    protected $patch;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->patch = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Cache';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        \array_map(static function ($value): void {
            @\unlink($value);
        }, \glob($this->patch . \DIRECTORY_SEPARATOR . '*', \GLOB_NOSORT));

        @\rmdir($this->patch);
    }

    public function testHandleNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('404 Not Found: Requested route [/].');

        $collection = new RouteCollection();

        $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/')
        );
    }

    public function testHandleStrictMatching(): void
    {
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

        try {
            $this->dispatcher->handle(
                $collection,
                (new ServerRequestFactory())->createServerRequest('GET', '/test///')
            );
        } catch (NotFoundException $e) {
            self::assertSame('404 Not Found: Requested route [/test///].', $e->getMessage());
        }

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test/')
        );

        self::assertSame('hello', (string) $response->getBody());
    }

    public function testHandleMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage('405 Method [GET,HEAD] Not Allowed: For requested route [/].');

        $collection = new RouteCollection();
        $route = new Route(
            'GET',
            '/',
            static function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody((new StreamFactory())->createStream('hello'));
            }
        );
        $route->setInvoker(new Invoker());

        $collection->add($route);

        $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('DELETE', '/')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
