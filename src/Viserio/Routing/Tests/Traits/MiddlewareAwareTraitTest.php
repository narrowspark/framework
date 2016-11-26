<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Traits;

use Viserio\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Routing\Traits\MiddlewareAwareTrait;

class MiddlewareAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MiddlewareAwareTrait;

    public function testWithAndWithoutMiddleware()
    {
        $this->withMiddleware(FooMiddleware::class);

        $this->assertSame([FooMiddleware::class], $this->middlewares);

        $this->withoutMiddleware(FooMiddleware::class);

        $this->assertSame([], $this->middlewares);
    }
}
