<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Routing\MiddlewareNameResolver;

/**
 * @internal
 */
final class MiddlewareNameResolverTest extends TestCase
{
    public function testResolveMiddleware(): void
    {
        $map = [
            'test' => new stdClass(),
        ];
        $middlewareGroups = [];

        static::assertSame($map['test'], MiddlewareNameResolver::resolve('test', $map, $middlewareGroups, []));
        static::assertSame('dontexists', MiddlewareNameResolver::resolve('dontexists', $map, $middlewareGroups, []));
    }

    public function testResolveWithBypassMiddleware(): void
    {
        $map = [
            'test' => new stdClass(),
        ];
        $middlewareGroups = [];

        static::assertSame([], MiddlewareNameResolver::resolve('test', $map, $middlewareGroups, ['test']));
    }

    public function testResolveWithBypassMiddlewareOnGroup(): void
    {
        $test2 = new stdClass();
        $map   = [
            'test'  => new stdClass(),
            'test2' => $test2,
        ];
        $middlewareGroups = [
            'web' => [
                'test',
                'test2',
            ],
        ];

        static::assertSame([$test2], MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, ['test']));
    }

    public function testResolveMiddlewareGroup(): void
    {
        $map = [
            'test'  => new stdClass(),
            'test2' => new stdClass(),
        ];
        $middlewareGroups = [
            'web' => [
                'test',
                'test2',
            ],
        ];

        static::assertSame(\array_values($map), MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, []));
    }

    public function testResolveMiddlewareGroupWitNestedGroup(): void
    {
        $map = [
            'test'     => new stdClass(),
            'test2'    => new stdClass(),
            'jsonTest' => new stdClass(),
        ];
        $middlewareGroups = [
            'web' => [
                'test',
                'test2',
                'json',
            ],
            'json' => [
                'jsonTest',
            ],
        ];

        static::assertSame(\array_values($map), MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, []));
    }
}
