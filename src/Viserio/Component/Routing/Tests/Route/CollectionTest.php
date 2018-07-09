<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Route;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection;
use Viserio\Component\Routing\Tests\Fixture\Controller;

/**
 * @internal
 */
final class CollectionTest extends TestCase
{
    public function testMatch(): void
    {
        $route = new Route('GET', '/collection', null);

        $collection = new Collection();

        static::assertInstanceOf(Route::class, $collection->add($route));

        static::assertInstanceOf(Route::class, $collection->match('GET|HEAD/collection'));

        static::assertSame(1, $collection->count());

        static::assertSame([$route], $collection->getRoutes());
    }

    public function testHasNamedRouteAndGetByName(): void
    {
        $route = new Route('GET', '/collection', ['as' => 'test']);

        $collection = new Collection();

        static::assertInstanceOf(Route::class, $collection->add($route));

        static::assertTrue($collection->hasNamedRoute('test'));

        static::assertInstanceOf(Route::class, $collection->getByName('test'));

        static::assertFalse($collection->hasNamedRoute('dont'));

        static::assertNull($collection->getByName('dont'));
    }

    public function testGetByAction(): void
    {
        $route = new Route('GET', '/collection', ['controller' => Controller::class]);

        $collection = new Collection();

        static::assertInstanceOf(Route::class, $collection->add($route));

        static::assertInstanceOf(Route::class, $collection->getByAction(\trim(Controller::class, '\\')));

        static::assertFalse($collection->hasNamedRoute('dont'));

        static::assertNull($collection->getByName('dont'));
    }
}
