<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Tests\Fixture\Controller;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;

/**
 * @internal
 */
final class ControllerTest extends TestCase
{
    public function testGatherMiddleware(): void
    {
        $controller = new Controller();

        $this->assertSame([], $controller->gatherMiddleware());

        $controller->withMiddleware(FooMiddleware::class);

        $this->assertSame([FooMiddleware::class => FooMiddleware::class], $controller->gatherMiddleware());
    }

    public function testGatherDisabledMiddleware(): void
    {
        $controller = new Controller();

        $this->assertSame([], $controller->gatherDisabledMiddleware());

        $controller->withoutMiddleware(FooMiddleware::class);

        $this->assertSame([FooMiddleware::class => true], $controller->gatherDisabledMiddleware());
    }

    public function testThrowsExceptionOnMissingMethods(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method [put] does not exist.');

        $controller = new Controller();
        $controller->put();
    }
}
