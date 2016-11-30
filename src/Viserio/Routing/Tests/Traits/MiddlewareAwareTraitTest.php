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

        self::assertSame([FooMiddleware::class], $this->middlewares);

        $this->withoutMiddleware(FooMiddleware::class);

        self::assertSame([], $this->middlewares);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage [Viserio\Routing\Tests\Traits\MiddlewareAwareTraitTest] should implement \Interop\Http\Middleware\ServerMiddlewareInterface
     */
    public function testWithAndWithoutMiddlewareToThrowException()
    {
        $this->withMiddleware(self::class);
    }
}
