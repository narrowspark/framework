<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Component\Routing\Tests\Fixture\MiddlewareHandler;

class MiddlewareAwareTraitTest extends TestCase
{
    public function testWithMiddlewareObject(): void
    {
        $middleware = new FooMiddleware();
        $object     = new MiddlewareHandler(true);

        $object->withMiddleware($middleware);

        self::assertSame([FooMiddleware::class => $middleware], $object->getMiddlewares());
    }

    public function testWithMiddlewareString(): void
    {
        $object = new MiddlewareHandler(true);

        $object->withMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $object->getMiddlewares());
    }

    public function testWithMiddlewareArray(): void
    {
        $object = new MiddlewareHandler(true);

        $object->withMiddleware([FooMiddleware::class, FakeMiddleware::class]);

        self::assertSame([FooMiddleware::class => FooMiddleware::class, FakeMiddleware::class => FakeMiddleware::class], $object->getMiddlewares());
    }

    public function testWithoutMiddlewareWithString(): void
    {
        $object = new MiddlewareHandler(true, true);

        $object->withoutMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => true], $object->getBypassedMiddlewares());
    }

    public function testWithoutMiddlewareWithArray(): void
    {
        $object = new MiddlewareHandler(true, true);

        $object->withoutMiddleware([FooMiddleware::class, FooMiddleware::class]);

        self::assertSame([FooMiddleware::class => true], $object->getBypassedMiddlewares());
    }

    public function testWithoutMiddlewareWithNull(): void
    {
        $object = new MiddlewareHandler(true, true);

        $object->withMiddleware(FooMiddleware::class);
        $object->withoutMiddleware(null);

        self::assertSame([], $object->getMiddlewares());
        self::assertSame([], $object->getBypassedMiddlewares());
    }

    public function testAliasMiddleware(): void
    {
        $object = new MiddlewareHandler(true);
        $object->aliasMiddleware('foo', FooMiddleware::class);

        self::assertSame(['foo' => FooMiddleware::class], $object->getMiddlewares());

        $middleware = new FooMiddleware();
        $object     = new MiddlewareHandler(true);

        $object->aliasMiddleware('bar', $middleware);

        self::assertSame(['bar' => $middleware], $object->getMiddlewares());
    }

    /**
     * @expectedException \Viserio\Component\Contract\Routing\Exception\RuntimeException
     * @expectedExceptionMessage Alias [foo] already exists.
     */
    public function testAliasMiddlewareThrowException(): void
    {
        $object = new MiddlewareHandler(true);

        $object->aliasMiddleware('foo', FooMiddleware::class);
        $object->aliasMiddleware('foo', FooMiddleware::class);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Routing\Exception\UnexpectedValueException
     * @expectedExceptionMessage Expected string or object; received [NULL].
     */
    public function testAliasMiddlewareThrowExceptionWithWrongType(): void
    {
        (new MiddlewareHandler(true))->aliasMiddleware('foo', null);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Routing\Exception\UnexpectedValueException
     * @expectedExceptionMessage Psr\Http\Server\MiddlewareInterface is not implemented in [Viserio\Component\Routing\Tests\Traits\MiddlewareAwareTraitTest].
     */
    public function testWithWrongMiddleware(): void
    {
        (new MiddlewareHandler(true, true))->withMiddleware(MiddlewareAwareTraitTest::class);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Routing\Exception\UnexpectedValueException
     * @expectedExceptionMessage Expected string, object or array; received [NULL].
     */
    public function testWithWrongType(): void
    {
        (new MiddlewareHandler(true))->withMiddleware(null);
    }
}
