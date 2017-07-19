<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;

class MiddlewareAwareTraitTest extends TestCase
{
    use MiddlewareAwareTrait;

    public function testWithMiddlewareObject(): void
    {
        $object = new FooMiddleware();

        $this->withMiddleware($object);

        self::assertSame([FooMiddleware::class => $object], $this->middlewares);
    }

    public function testWithMiddlewareString(): void
    {
        //reset
        $this->middlewares = [];

        $this->withMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $this->middlewares);
    }

    public function testWithMiddlewareArray(): void
    {
        //reset
        $this->middlewares = [];

        $this->withMiddleware([FooMiddleware::class, FakeMiddleware::class]);

        self::assertSame([FooMiddleware::class => FooMiddleware::class, FakeMiddleware::class => FakeMiddleware::class], $this->middlewares);
    }

    public function testWithoutMiddlewareWithString(): void
    {
        //reset
        $this->bypassedMiddlewares = [];

        $this->withoutMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $this->bypassedMiddlewares);
    }

    public function testWithoutMiddlewareWithArray(): void
    {
        //reset
        $this->bypassedMiddlewares = [];

        $this->withoutMiddleware([FooMiddleware::class, FooMiddleware::class]);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $this->bypassedMiddlewares);
    }

    public function testWithoutMiddlewareWithNull(): void
    {
        //reset
        $this->middlewares         = [];
        $this->bypassedMiddlewares = [];

        $this->withMiddleware(FooMiddleware::class);
        $this->withoutMiddleware();

        self::assertSame([], $this->middlewares);
        self::assertSame([], $this->bypassedMiddlewares);
    }

    public function testAliasMiddleware(): void
    {
        //reset
        $this->middlewares = [];

        $this->aliasMiddleware('foo', FooMiddleware::class);

        self::assertSame(['foo' => FooMiddleware::class], $this->middlewares);

        //reset
        $this->middlewares = [];

        $object = new FooMiddleware();

        $this->aliasMiddleware('bar', $object);

        self::assertSame(['bar' => $object], $this->middlewares);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Alias [foo] already exists.
     */
    public function testAliasMiddlewareThrowException(): void
    {
        //reset
        $this->middlewares = [];

        $this->aliasMiddleware('foo', FooMiddleware::class);
        $this->aliasMiddleware('foo', FooMiddleware::class);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Expected string or object; received [NULL].
     */
    public function testAliasMiddlewareThrowExceptionWithWrongType(): void
    {
        //reset
        $this->middlewares = [];

        $this->aliasMiddleware('foo', null);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Interop\Http\ServerMiddleware\MiddlewareInterface is not implemented in [Viserio\Component\Routing\Tests\Traits\MiddlewareAwareTraitTest].
     */
    public function testWithWrongMiddleware(): void
    {
        $this->withMiddleware(MiddlewareAwareTraitTest::class);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Expected string, object or array; received [NULL].
     */
    public function testWithWrongType(): void
    {
        $this->withMiddleware(null);
    }
}
