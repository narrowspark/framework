<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Routing\MiddlewareNameResolver;

class MiddlewareNameResolverTest extends TestCase
{
    public function testRolveMiddleware()
    {
        $map = [
            'test' => new stdClass(),
        ];
        $middlewareGroups = [];

        self::assertSame($map['test'], MiddlewareNameResolver::resolve('test', $map, $middlewareGroups, []));
        self::assertSame('dontexists', MiddlewareNameResolver::resolve('dontexists', $map, $middlewareGroups, []));
    }

    public function testRolveMiddlewareGroup()
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

        self::assertSame(array_values($map), MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, []));
    }

    public function testRolveMiddlewareGroupWitNestedGroup()
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

        self::assertSame(array_values($map), MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, []));
    }
}
