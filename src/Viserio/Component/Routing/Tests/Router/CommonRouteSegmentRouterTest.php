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

use Viserio\Component\Routing\Tests\Router\Traits\TestRouter404Trait;
use Viserio\Contract\Routing\Pattern;
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 *
 * @small
 */
final class CommonRouteSegmentRouterTest extends AbstractRouterBaseTest
{
    use TestRouter404Trait;

    public function provideRouterCases(): iterable
    {
        return [
            ['GET', '/route1/a/b/c', 'route1 | p1 = a | p2 = b | p3 = c'],
            ['GET', '/route2/a/b/c', 'route2 | p1 = a | p2 = b | p3 = c'],
            ['GET', '/route3/a/b/c', 'route3 | p1 = a | p2 = b | p3 = c'],
            ['GET', '/route4/a/b/c', 'route4 | p1 = a | p2 = b | p3 = c'],
            ['GET', '/route5/a/b/c', 'route5 | p_1 = a | p_2 = b | p_3 = c'],
            ['GET', '/route6/a/b/c', 'route6 | p_1 = a | p2 = b | p_3 = c'],
        ];
    }

    public function provideRouter404Cases(): iterable
    {
        return [
            ['GET', '/route6/a/1/c'],
            ['GET', '/route1/a/123/c'],
        ];
    }

    protected function definitions(RouterContract $router): void
    {
        $router->pattern('p2', Pattern::ALPHA);

        $router->get('/route1/{p1}/{p2}/{p3}', function ($request, $name, $p1, $p2, $p3) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | p1 = ' . $p1 . ' | p2 = ' . $p2 . ' | p3 = ' . $p3)
                );
        })->addParameter('name', 'route1');

        $router->get('/route2/{p1}/{p2}/{p3}', function ($request, $name, $p1, $p2, $p3) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | p1 = ' . $p1 . ' | p2 = ' . $p2 . ' | p3 = ' . $p3)
                );
        })->addParameter('name', 'route2');

        $router->get('/route3/{p1}/{p2}/{p3}', function ($request, $name, $p1, $p2, $p3) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | p1 = ' . $p1 . ' | p2 = ' . $p2 . ' | p3 = ' . $p3)
                );
        })->addParameter('name', 'route3');

        $router->get('/route4/{p1}/{p2}/{p3}', function ($request, $name, $p1, $p2, $p3) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | p1 = ' . $p1 . ' | p2 = ' . $p2 . ' | p3 = ' . $p3)
                );
        })->addParameter('name', 'route4');

        $router->get('/route5/{p_1}/{p_2}/{p_3}', function ($request, $name, $p_1, $p_2, $p_3) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | p_1 = ' . $p_1 . ' | p_2 = ' . $p_2 . ' | p_3 = ' . $p_3)
                );
        })->addParameter('name', 'route5');

        $router->get('/route6/{p_1}/{p2}/{p_3}', function ($request, $name, $p_1, $p2, $p_3) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | p_1 = ' . $p_1 . ' | p2 = ' . $p2 . ' | p_3 = ' . $p_3)
                );
        })->addParameter('name', 'route6');
    }
}
