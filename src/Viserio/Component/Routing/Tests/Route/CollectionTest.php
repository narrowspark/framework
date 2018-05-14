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

namespace Viserio\Component\Routing\Tests\Route;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection;
use Viserio\Component\Routing\Tests\Fixture\Controller;

/**
 * @internal
 *
 * @small
 */
final class CollectionTest extends TestCase
{
    public function testMatch(): void
    {
        $route = new Route('GET', '/collection', null);

        $collection = new Collection();

        self::assertInstanceOf(Route::class, $collection->add($route));

        self::assertInstanceOf(Route::class, $collection->match('GET|HEAD/collection'));

        self::assertSame(1, $collection->count());

        self::assertSame([$route], $collection->getRoutes());
    }

    public function testHasNamedRouteAndGetByName(): void
    {
        $route = new Route('GET', '/collection', ['as' => 'test']);

        $collection = new Collection();

        self::assertInstanceOf(Route::class, $collection->add($route));

        self::assertTrue($collection->hasNamedRoute('test'));

        self::assertInstanceOf(Route::class, $collection->getByName('test'));

        self::assertFalse($collection->hasNamedRoute('dont'));

        self::assertNull($collection->getByName('dont'));
    }

    public function testGetByAction(): void
    {
        $route = new Route('GET', '/collection', ['controller' => Controller::class]);

        $collection = new Collection();

        self::assertInstanceOf(Route::class, $collection->add($route));

        self::assertInstanceOf(Route::class, $collection->getByAction(\trim(Controller::class, '\\')));

        self::assertFalse($collection->hasNamedRoute('dont'));

        self::assertNull($collection->getByName('dont'));
    }
}
