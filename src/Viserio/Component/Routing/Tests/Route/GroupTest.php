<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Route\Group;

/**
 * @internal
 */
final class GroupTest extends TestCase
{
    public function testGroupMerging(): void
    {
        $old = ['prefix' => 'foo/bar/'];
        static::assertEquals(
            ['prefix' => 'foo/bar/baz', 'suffix' => null, 'namespace' => null, 'where' => []],
            Group::merge(['prefix' => 'baz'], $old)
        );

        $old = ['suffix' => '.bar'];
        static::assertEquals(
            ['prefix' => null, 'suffix' => '.foo.bar', 'namespace' => null, 'where' => []],
            Group::merge(['suffix' => '.foo'], $old)
        );

        $old = ['domain' => 'foo'];
        static::assertEquals(
            ['domain' => 'baz', 'prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => []],
            Group::merge(['domain' => 'baz'], $old)
        );

        $old = ['as' => 'foo.'];
        static::assertEquals(
            ['as' => 'foo.bar', 'prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => []],
            Group::merge(['as' => 'bar'], $old)
        );

        $old = ['where' => ['var1' => 'foo', 'var2' => 'bar']];
        static::assertEquals(
            ['prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => [
                'var1' => 'foo', 'var2' => 'baz', 'var3' => 'qux',
            ]],
            Group::merge(['where' => ['var2' => 'baz', 'var3' => 'qux']], $old)
        );

        $old = [];
        static::assertEquals(
            ['prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => [
                'var1' => 'foo', 'var2' => 'bar',
            ]],
            Group::merge(['where' => ['var1' => 'foo', 'var2' => 'bar']], $old)
        );
    }
}
