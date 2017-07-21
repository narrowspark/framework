<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Routing\MiddlewareAware as MiddlewareAwareContract;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;

class MiddlewareAwareTraitTest extends TestCase
{
    public function testWithMiddlewareObject(): void
    {
        $middleware = new FooMiddleware();
        $object     = $this->getMiddlewareAwareObject(true);

        $object->withMiddleware($middleware);

        self::assertSame([FooMiddleware::class => $middleware], $object->getMiddlewares());
    }

    public function testWithMiddlewareString(): void
    {
        $object = $this->getMiddlewareAwareObject(true);

        $object->withMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $object->getMiddlewares());
    }

    public function testWithMiddlewareArray(): void
    {
        $object = $this->getMiddlewareAwareObject(true);

        $object->withMiddleware([FooMiddleware::class, FakeMiddleware::class]);

        self::assertSame([FooMiddleware::class => FooMiddleware::class, FakeMiddleware::class => FakeMiddleware::class], $object->getMiddlewares());
    }

    public function testWithoutMiddlewareWithString(): void
    {
        $object = $this->getMiddlewareAwareObject(true, true);

        $object->withoutMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => true], $object->getBypassedMiddlewares());
    }

    public function testWithoutMiddlewareWithArray(): void
    {
        $object = $this->getMiddlewareAwareObject(true, true);

        $object->withoutMiddleware([FooMiddleware::class, FooMiddleware::class]);

        self::assertSame([FooMiddleware::class => true], $object->getBypassedMiddlewares());
    }

    public function testWithoutMiddlewareWithNull(): void
    {
        $object = $this->getMiddlewareAwareObject(true, true);

        $object->withMiddleware(FooMiddleware::class);
        $object->withoutMiddleware(null);

        self::assertSame([], $object->getMiddlewares());
        self::assertSame([], $object->getBypassedMiddlewares());
    }

    public function testAliasMiddleware(): void
    {
        $object = $this->getMiddlewareAwareObject(true);
        $object->aliasMiddleware('foo', FooMiddleware::class);

        self::assertSame(['foo' => FooMiddleware::class], $object->getMiddlewares());

        $middleware = new FooMiddleware();
        $object     = $this->getMiddlewareAwareObject(true);

        $object->aliasMiddleware('bar', $middleware);

        self::assertSame(['bar' => $middleware], $object->getMiddlewares());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Alias [foo] already exists.
     */
    public function testAliasMiddlewareThrowException(): void
    {
        $object = $this->getMiddlewareAwareObject(true);

        $object->aliasMiddleware('foo', FooMiddleware::class);
        $object->aliasMiddleware('foo', FooMiddleware::class);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Expected string or object; received [NULL].
     */
    public function testAliasMiddlewareThrowExceptionWithWrongType(): void
    {
        $this->getMiddlewareAwareObject(true)->aliasMiddleware('foo', null);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Interop\Http\ServerMiddleware\MiddlewareInterface is not implemented in [Viserio\Component\Routing\Tests\Traits\MiddlewareAwareTraitTest].
     */
    public function testWithWrongMiddleware(): void
    {
        $this->getMiddlewareAwareObject(true, true)->withMiddleware(MiddlewareAwareTraitTest::class);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Expected string, object or array; received [NULL].
     */
    public function testWithWrongType(): void
    {
        $this->getMiddlewareAwareObject(true)->withMiddleware(null);
    }

    /**
     * @param bool $resetMiddlewares
     * @param bool $resetBypassedMiddlewares
     *
     * @return \Viserio\Component\Contracts\Routing\MiddlewareAware
     */
    private function getMiddlewareAwareObject(bool $resetMiddlewares = false, bool $resetBypassedMiddlewares = false)
    {
        return new class($resetMiddlewares, $resetBypassedMiddlewares) implements MiddlewareAwareContract {
            use MiddlewareAwareTrait;

            public function __construct($resetMiddlewares, $resetBypassedMiddlewares)
            {
                if ($resetMiddlewares) {
                    $this->middlewares = [];
                }

                if ($resetBypassedMiddlewares) {
                    $this->bypassedMiddlewares = [];
                }
            }

            /**
             * @return array
             */
            public function getMiddlewares(): array
            {
                return $this->middlewares;
            }

            /**
             * @return array
             */
            public function getBypassedMiddlewares(): array
            {
                return $this->bypassedMiddlewares;
            }
        };
    }
}
