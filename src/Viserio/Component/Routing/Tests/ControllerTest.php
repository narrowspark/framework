<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Tests\Fixture\Controller;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;

class ControllerTest extends TestCase
{
    public function testGatherMiddleware()
    {
        $controller = new Controller();

        self::assertSame([], $controller->gatherMiddleware());

        $controller->withMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $controller->gatherMiddleware());
    }

    public function testGatherDisabledMiddlewares()
    {
        $controller = new Controller();

        self::assertSame([], $controller->gatherDisabledMiddlewares());

        $controller->withoutMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => true], $controller->gatherDisabledMiddlewares());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Method [put] does not exist.
     */
    public function testThrowsExceptionOnMissingMethods()
    {
        $controller = new Controller();
        $controller->put();
    }
}
