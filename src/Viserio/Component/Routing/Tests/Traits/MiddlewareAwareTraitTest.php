<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;

class MiddlewareAwareTraitTest extends TestCase
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
     * @expectedExceptionMessage \Interop\Http\ServerMiddleware\MiddlewareInterface is not implemented in [Viserio\Component\Routing\Tests\Traits\MiddlewareAwareTraitTest].
     */
    public function testWithWrongMiddleware()
    {
        $this->withMiddleware(MiddlewareAwareTraitTest::class);
    }
}
