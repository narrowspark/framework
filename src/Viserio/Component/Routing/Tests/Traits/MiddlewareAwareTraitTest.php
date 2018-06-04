<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Component\Routing\Tests\Fixture\MiddlewareHandler;

/**
 * @internal
 */
final class MiddlewareAwareTraitTest extends TestCase
{
    public function testWithMiddlewareObject(): void
    {
        $middleware = new FooMiddleware();
        $object     = new MiddlewareHandler(true);

        $object->withMiddleware($middleware);

        $this->assertSame([FooMiddleware::class => $middleware], $object->getMiddleware());
    }

    public function testWithMiddlewareString(): void
    {
        $object = new MiddlewareHandler(true);

        $object->withMiddleware(FooMiddleware::class);

        $this->assertSame([FooMiddleware::class => FooMiddleware::class], $object->getMiddleware());
    }

    public function testWithMiddlewareArray(): void
    {
        $object = new MiddlewareHandler(true);

        $object->withMiddleware([FooMiddleware::class, FakeMiddleware::class]);

        $this->assertSame([FooMiddleware::class => FooMiddleware::class, FakeMiddleware::class => FakeMiddleware::class], $object->getMiddleware());
    }

    public function testWithoutMiddlewareWithString(): void
    {
        $object = new MiddlewareHandler(true, true);

        $object->withoutMiddleware(FooMiddleware::class);

        $this->assertSame([FooMiddleware::class => true], $object->getBypassedMiddleware());
    }

    public function testWithoutMiddlewareWithArray(): void
    {
        $object = new MiddlewareHandler(true, true);

        $object->withoutMiddleware([FooMiddleware::class, FooMiddleware::class]);

        $this->assertSame([FooMiddleware::class => true], $object->getBypassedMiddleware());
    }

    public function testWithoutMiddlewareWithNull(): void
    {
        $object = new MiddlewareHandler(true, true);

        $object->withMiddleware(FooMiddleware::class);
        $object->withoutMiddleware(null);

        $this->assertSame([], $object->getMiddleware());
        $this->assertSame([], $object->getBypassedMiddleware());
    }

    public function testAliasMiddleware(): void
    {
        $object = new MiddlewareHandler(true);
        $object->aliasMiddleware('foo', FooMiddleware::class);

        $this->assertSame(['foo' => FooMiddleware::class], $object->getMiddleware());

        $middleware = new FooMiddleware();
        $object     = new MiddlewareHandler(true);

        $object->aliasMiddleware('bar', $middleware);

        $this->assertSame(['bar' => $middleware], $object->getMiddleware());
    }

    public function testAliasMiddlewareThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Routing\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Alias [foo] already exists.');

        $object = new MiddlewareHandler(true);

        $object->aliasMiddleware('foo', FooMiddleware::class);
        $object->aliasMiddleware('foo', FooMiddleware::class);
    }

    public function testAliasMiddlewareThrowExceptionWithWrongType(): void
    {
        $this->expectException(\Viserio\Component\Contract\Routing\Exception\UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected string or object; received [NULL].');

        (new MiddlewareHandler(true))->aliasMiddleware('foo', null);
    }

    public function testWithWrongMiddleware(): void
    {
        $this->expectException(\Viserio\Component\Contract\Routing\Exception\UnexpectedValueException::class);
        $this->expectExceptionMessage('Psr\\Http\\Server\\MiddlewareInterface is not implemented in [Viserio\\Component\\Routing\\Tests\\Traits\\MiddlewareAwareTraitTest].');

        (new MiddlewareHandler(true, true))->withMiddleware(MiddlewareAwareTraitTest::class);
    }

    public function testWithWrongType(): void
    {
        $this->expectException(\Viserio\Component\Contract\Routing\Exception\UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected string, object or array; received [NULL].');

        (new MiddlewareHandler(true))->withMiddleware(null);
    }
}
