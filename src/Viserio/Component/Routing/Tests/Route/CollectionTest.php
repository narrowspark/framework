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

        $this->assertInstanceOf(Route::class, $collection->add($route));

        $this->assertInstanceOf(Route::class, $collection->match('GET|HEAD/collection'));

        $this->assertSame(1, $collection->count());

        $this->assertSame([$route], $collection->getRoutes());
    }

    public function testHasNamedRouteAndGetByName(): void
    {
        $route = new Route('GET', '/collection', ['as' => 'test']);

        $collection = new Collection();

        $this->assertInstanceOf(Route::class, $collection->add($route));

        $this->assertTrue($collection->hasNamedRoute('test'));

        $this->assertInstanceOf(Route::class, $collection->getByName('test'));

        $this->assertFalse($collection->hasNamedRoute('dont'));

        $this->assertNull($collection->getByName('dont'));
    }

    public function testGetByAction(): void
    {
        $route = new Route('GET', '/collection', ['controller' => Controller::class]);

        $collection = new Collection();

        $this->assertInstanceOf(Route::class, $collection->add($route));

        $this->assertInstanceOf(Route::class, $collection->getByAction(\trim(Controller::class, '\\')));

        $this->assertFalse($collection->hasNamedRoute('dont'));

        $this->assertNull($collection->getByName('dont'));
    }
}
