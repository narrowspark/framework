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

        $this->assertSame($map['test'], MiddlewareNameResolver::resolve('test', $map, $middlewareGroups, []));
        $this->assertSame('dontexists', MiddlewareNameResolver::resolve('dontexists', $map, $middlewareGroups, []));
    }

    public function testResolveWithBypassMiddleware(): void
    {
        $map = [
            'test' => new stdClass(),
        ];
        $middlewareGroups = [];

        $this->assertSame([], MiddlewareNameResolver::resolve('test', $map, $middlewareGroups, ['test']));
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

        $this->assertSame([$test2], MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, ['test']));
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

        $this->assertSame(\array_values($map), MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, []));
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

        $this->assertSame(\array_values($map), MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, []));
    }
}
