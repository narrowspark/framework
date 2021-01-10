<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Routing\MiddlewareNameResolver;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class MiddlewareNameResolverTest extends TestCase
{
    public function testResolveMiddleware(): void
    {
        $map = [
            'test' => new stdClass(),
        ];
        $middlewareGroups = [];

        self::assertSame($map['test'], MiddlewareNameResolver::resolve('test', $map, $middlewareGroups, []));
        self::assertSame('dontexists', MiddlewareNameResolver::resolve('dontexists', $map, $middlewareGroups, []));
    }

    public function testResolveWithBypassMiddleware(): void
    {
        $map = [
            'test' => new stdClass(),
        ];
        $middlewareGroups = [];

        self::assertSame([], MiddlewareNameResolver::resolve('test', $map, $middlewareGroups, ['test']));
    }

    public function testResolveWithBypassMiddlewareOnGroup(): void
    {
        $test2 = new stdClass();
        $map = [
            'test' => new stdClass(),
            'test2' => $test2,
        ];
        $middlewareGroups = [
            'web' => [
                'test',
                'test2',
            ],
        ];

        self::assertSame([$test2], MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, ['test']));
    }

    public function testResolveMiddlewareGroup(): void
    {
        $map = [
            'test' => new stdClass(),
            'test2' => new stdClass(),
        ];
        $middlewareGroups = [
            'web' => [
                'test',
                'test2',
            ],
        ];

        self::assertSame(\array_values($map), MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, []));
    }

    public function testResolveMiddlewareGroupWitNestedGroup(): void
    {
        $map = [
            'test' => new stdClass(),
            'test2' => new stdClass(),
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

        self::assertSame(\array_values($map), MiddlewareNameResolver::resolve('web', $map, $middlewareGroups, []));
    }
}
