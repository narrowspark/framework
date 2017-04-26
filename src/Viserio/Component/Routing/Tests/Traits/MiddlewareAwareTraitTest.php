<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Traits;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;

class MiddlewareAwareTraitTest extends TestCase
{
    use MiddlewareAwareTrait;

    public function testWithMiddleware()
    {
        $this->withMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class], $this->middlewares);

        $this->withoutMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class], $this->middlewares);
    }

    public function testWithoutMiddleware()
    {
        $this->withMiddleware(FooMiddleware::class);

        $this->withoutMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class], $this->bypassedMiddlewares);
    }

    public function testAliasWithoutMiddleware()
    {
        $this->aliasMiddleware('foo', FooMiddleware::class);

        self::assertSame(['foo' => FooMiddleware::class], $this->middlewares);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Interop\Http\ServerMiddleware\MiddlewareInterface is not implemented in [Viserio\Component\Routing\Tests\Traits\MiddlewareAwareTraitTest].
     */
    public function testWithWrongMiddleware()
    {
        $this->withMiddleware(MiddlewareAwareTraitTest::class);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Expected string or array; received [object].
     */
    public function testWithWrongParamObject()
    {
        $this->withMiddleware(new stdClass());
    }
}
