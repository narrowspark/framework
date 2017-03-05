<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Route\Group;

class GroupTest extends TestCase
{
    public function testGroupMerging()
    {
        $old = ['prefix' => 'foo/bar/'];
        self::assertEquals(
            ['prefix' => 'foo/bar/baz', 'suffix' => null, 'namespace' => null, 'where' => []],
            Group::merge(['prefix' => 'baz'], $old)
        );

        $old = ['suffix' => '.bar'];
        self::assertEquals(
            ['prefix' => null, 'suffix' => '.foo.bar', 'namespace' => null, 'where' => []],
            Group::merge(['suffix' => '.foo'], $old)
        );

        $old = ['domain' => 'foo'];
        self::assertEquals(
            ['domain' => 'baz', 'prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => []],
            Group::merge(['domain' => 'baz'], $old)
        );

        $old = ['as' => 'foo.'];
        self::assertEquals(
            ['as' => 'foo.bar', 'prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => []],
            Group::merge(['as' => 'bar'], $old)
        );

        $old = ['where' => ['var1' => 'foo', 'var2' => 'bar']];
        self::assertEquals(
            ['prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => [
                'var1' => 'foo', 'var2' => 'baz', 'var3' => 'qux',
            ]],
            Group::merge(['where' => ['var2' => 'baz', 'var3' => 'qux']], $old)
        );

        $old = [];
        self::assertEquals(
            ['prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => [
                'var1' => 'foo', 'var2' => 'bar',
            ]],
            Group::merge(['where' => ['var1' => 'foo', 'var2' => 'bar']], $old)
        );
    }
}
