<?php

namespace Brainwave\Support\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Support\Arr;
use Brainwave\Support\Helper;
use Brainwave\Support\Str;

/**
 * SupportHelpersTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class SupportHelpersTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayBuild()
    {
        $this->assertEquals(['foo' => 'bar'], Arr::build(['foo' => 'bar'], function ($key, $value) {
            return [$key, $value];
        }));
    }

    public function testArrayDot()
    {
        $array = Arr::dot(['name' => 'daniel', 'languages' => ['php' => true]]);
        $this->assertEquals($array, ['name' => 'daniel', 'languages.php' => true]);

        $array = Arr::dot(['name' => 'daniel', 'languages' => ['javascript' => true]]);
        $this->assertEquals($array, ['name' => 'daniel', 'languages.javascript' => true]);
    }

    public function testArrayPluckWithNestedKeys()
    {
        $array = [['user' => ['narrowspark', 'brainwave']], ['user' => ['dayle', 'rees']]];
        $this->assertEquals(['narrowspark', 'dayle'], Arr::pluck($array, 'user.0'));
        $this->assertEquals(['narrowspark', 'dayle'], Arr::pluck($array, ['user', 0]));
        $this->assertEquals(['narrowspark' => 'brainwave', 'dayle' => 'rees'], Arr::pluck($array, 'user.1', 'user.0'));
        $this->assertEquals(['narrowspark' => 'brainwave', 'dayle' => 'rees'], Arr::pluck($array, ['user', 1], ['user', 0]));
    }

    public function testArrayGet()
    {
        $array = ['names' => ['developer' => 'daniel']];
        $this->assertEquals('daniel', Arr::get($array, 'names.developer'));
        $this->assertEquals('david', Arr::get($array, 'names.otherDeveloper', 'david'));
        $this->assertEquals('david', Arr::get($array, 'names.otherDeveloper', function () { return 'david'; }));
    }

    public function testArrayHas()
    {
        $array = ['names' => ['developer' => 'daniel']];
        $this->assertTrue(Arr::has($array, 'names'));
        $this->assertTrue(Arr::has($array, 'names.developer'));
        $this->assertFalse(Arr::has($array, 'foo'));
        $this->assertFalse(Arr::has($array, 'foo.bar'));
    }

    public function testArraySet()
    {
        $array = [];
        Arr::set($array, 'names.developer', 'daniel');
        $this->assertEquals('daniel', $array['names']['developer']);
    }

    public function testArrayForget()
    {
        $array = ['names' => ['developer' => 'daniel', 'otherDeveloper' => 'david']];
        Arr::forget($array, 'names.developer');
        $this->assertFalse(isset($array['names']['developer']));
        $this->assertTrue(isset($array['names']['otherDeveloper']));
        $array = ['names' => ['developer' => 'daniel', 'otherDeveloper' => 'david', 'thirdDeveloper' => 'dMax']];
        Arr::forget($array, ['names.developer', 'names.otherDeveloper']);
        $this->assertFalse(isset($array['names']['developer']));
        $this->assertFalse(isset($array['names']['otherDeveloper']));
        $this->assertTrue(isset($array['names']['thirdDeveloper']));
        $array = ['names' => ['developer' => 'daniel', 'otherDeveloper' => 'david'], 'otherNames' => ['developer' => 'dMax', 'otherDeveloper' => 'Graham']];
        Arr::forget($array, ['names.developer', 'otherNames.otherDeveloper']);
        $expected = ['names' => ['otherDeveloper' => 'david'], 'otherNames' => ['developer' => 'dMax']];
        $this->assertEquals($expected, $array);
    }

    public function testArrayPluckWithArrayAndObjectValues()
    {
        $array = [(object) ['name' => 'daniel', 'email' => 'foo'], ['name' => 'david', 'email' => 'bar']];
        $this->assertEquals(['daniel', 'david'], Arr::pluck($array, 'name'));
        $this->assertEquals(['daniel' => 'foo', 'david' => 'bar'], Arr::pluck($array, 'email', 'name'));
    }

    public function testArrayExcept()
    {
        $array = ['name' => 'narrowspark', 'age' => 26];
        $this->assertEquals(['age' => 26], Arr::except($array, ['name']));
        $this->assertEquals(['age' => 26], Arr::except($array, 'name'));

        $array = ['name' => 'narrowspark', 'framework' => ['language' => 'PHP', 'name' => 'brainwave']];
        $this->assertEquals(['name' => 'narrowspark'], Arr::except($array, 'framework'));
        $this->assertEquals(['name' => 'narrowspark', 'framework' => ['name' => 'brainwave']], Arr::except($array, 'framework.language'));
        $this->assertEquals(['framework' => ['language' => 'PHP']], Arr::except($array, ['name', 'framework.name']));
    }

    public function testArrayOnly()
    {
        $array = ['name' => 'daniel', 'age' => 26];
        $this->assertEquals(['name' => 'daniel'], Arr::only($array, ['name']));
        $this->assertSame([], Arr::only($array, ['nonExistingKey']));
    }

    public function testArrayDivide()
    {
        $array = ['name' => 'daniel'];
        list($keys, $values) = Arr::divide($array);
        $this->assertEquals(['name'], $keys);
        $this->assertEquals(['daniel'], $values);
    }

    public function testArrayFirst()
    {
        $array = ['name' => 'daniel', 'otherDeveloper' => 'david'];
        $this->assertEquals('david', Arr::first($array, function ($key, $value) { return $value === 'david'; }));
    }

    public function testArrayFlatten()
    {
        $this->assertEquals(['#foo', '#bar', '#baz'], Arr::flatten([['#foo', '#bar'], ['#baz']]));
    }

    public function testStrIs()
    {
        $this->assertTrue(Str::is('*.dev', 'localhost.dev'));
        $this->assertTrue(Str::is('a', 'a'));
        $this->assertTrue(Str::is('/', '/'));
        $this->assertTrue(Str::is('*dev*', 'localhost.dev'));
        $this->assertTrue(Str::is('foo?bar', 'foo?bar'));
        $this->assertFalse(Str::is('*something', 'foobar'));
        $this->assertFalse(Str::is('foo', 'bar'));
        $this->assertFalse(Str::is('foo.*', 'foobar'));
        $this->assertFalse(Str::is('foo.ar', 'foobar'));
        $this->assertFalse(Str::is('foo?bar', 'foobar'));
        $this->assertFalse(Str::is('foo?bar', 'fobar'));
    }

    public function testClassBasename()
    {
        $this->assertEquals('Baz', Helper::classBasename('Foo\Bar\Baz'));
        $this->assertEquals('Baz', Helper::classBasename('Baz'));
    }

    public function testValue()
    {
        $this->assertEquals('foo', Helper::value('foo'));
        $this->assertEquals('foo', Helper::value(function () { return 'foo'; }));
    }

    public function testObjectGet()
    {
        $class = new \StdClass();
        $class->name = new \StdClass();
        $class->name->first = 'daniel';
        $this->assertEquals('daniel', Helper::objectGet($class, 'name.first'));
    }

    public function testGet()
    {
        $object = (object) ['users' => ['name' => ['daniel', 'Brainwave']]];
        $array = [(object) ['users' => [(object) ['name' => 'daniel']]]];
        $dottedArray = ['users' => ['first.name' => 'Daniel']];

        $arrayAccess = new SupportTestArrayAccess(['price' => 56, 'user' => new SupportTestArrayAccess(['name' => 'John'])]);
        $this->assertEquals('daniel', Arr::get($object, 'users.name.0'));
        $this->assertEquals('daniel', Arr::get($array, '0.users.0.name'));
        $this->assertNull(Arr::get($array, '0.users.3'));
        $this->assertEquals('Not found', Arr::get($array, '0.users.3', 'Not found'));
        $this->assertEquals('Not found', Arr::get($array, '0.users.3', function () { return 'Not found'; }));
        $this->assertEquals('Daniel', Arr::get($dottedArray, ['users', 'first.name']));
        $this->assertEquals('Not found', Arr::get($dottedArray, ['users', 'last.name'], 'Not found'));
        $this->assertEquals(56, Arr::get($arrayAccess, 'price'));
        $this->assertEquals('John', Arr::get($arrayAccess, 'user.name'));
        $this->assertEquals('void', Arr::get($arrayAccess, 'foo', 'void'));
        $this->assertEquals('void', Arr::get($arrayAccess, 'user.foo', 'void'));
        $this->assertNull(Arr::get($arrayAccess, 'foo'));
        $this->assertNull(Arr::get($arrayAccess, 'user.foo'));
    }

    public function testArrayWhere()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8];
        $this->assertEquals(['b' => 2, 'd' => 4, 'f' => 6, 'h' => 8], Arr::where(
            $array,
            function ($key, $value) {
                return $value % 2 === 0;
            }
        ));
    }

    public function testArrayPluckWithNestedArrays()
    {
        $array = [['account' => 'a', 'users' => [
            ['first' => 'foo', 'last' => 'bar', 'email' => 'foobar@test.com'],
        ]],['account' => 'b', 'users' => [
            ['first' => 'abigail', 'last' => 'bar'],
            ['first' => 'dayle', 'last' => 'rees'],
        ]]];

        $this->assertEquals([['foo'], ['abigail', 'dayle']], Arr::pluck($array, 'users.*.first'));
        $this->assertEquals(['a' => ['foo'], 'b' => ['abigail', 'dayle']], Arr::pluck($array, 'users.*.first', 'account'));
        $this->assertEquals([['foobar@test.com'], [null, null]], Arr::pluck($array, 'users.*.email'));
    }

    public function testClassUsesRecursiveShouldReturnTraitsOnParentClasses()
    {
        $this->assertEquals(
            [
                'SupportTestTraitOne' => 'SupportTestTraitOne',
                'SupportTestTraitTwo' => 'SupportTestTraitTwo',
            ],
            Helper::classUsesRecursive('SupportTestClassTwo')
        );
    }

    public function testArrayAdd()
    {
        $this->assertEquals(['surname' => 'Mövsümov'], Arr::add([], 'surname', 'Mövsümov'));
        $this->assertEquals(['developer' => ['name' => 'Ferid']], Arr::add([], 'developer.name', 'Ferid'));
    }

    public function testArrayPull()
    {
        $developer = ['firstname' => 'Ferid', 'surname' => 'Mövsümov'];
        $this->assertEquals('Mövsümov', Arr::pull($developer, 'surname'));
        $this->assertEquals(['firstname' => 'Ferid'], $developer);
    }

    public function testDataGetWithNestedArrays()
    {
        $array = [['name' => 'foo', 'email' => 'foobar@test.com'], ['name' => 'abigail'], ['name' => 'dayle']];

        $this->assertEquals(['foo', 'abigail', 'dayle'], Arr::dataGet($array, '*.name'));
        $this->assertEquals(['foobar@test.com', null, null], Arr::dataGet($array, '*.email', 'irrelevant'));

        $array = ['users' => [
            ['first' => 'foo', 'last' => 'bar', 'email' => 'foobar@test.com'],
            ['first' => 'abigail', 'last' => 'bar'],
            ['first' => 'dayle', 'last' => 'rees'],
        ], 'posts' => null];

        $this->assertEquals(['foo', 'abigail', 'dayle'], Arr::dataGet($array, 'users.*.first'));
        $this->assertEquals(['foobar@test.com', null, null], Arr::dataGet($array, 'users.*.email', 'irrelevant'));
        $this->assertEquals('not found', Arr::dataGet($array, 'posts.*.date', 'not found'));
        $this->assertEquals(null, Arr::dataGet($array, 'posts.*.date'));
    }

    public function testDataGetWithDoubleNestedArraysCollapsesResult()
    {
        $array = ['posts' => [
            ['comments' => [
                ['author' => 'foo', 'likes' => 4], ['author' => 'abigail', 'likes' => 3],
            ]], ['comments' => [
                ['author' => 'abigail', 'likes' => 2], ['author' => 'dayle'],
            ]], ['comments' => [
                ['author' => 'dayle'], ['author' => 'foo', 'likes' => 1],
            ]],
        ]];

        $this->assertEquals(['foo', 'abigail', 'abigail', 'dayle', 'dayle', 'foo'], Arr::dataGet($array, 'posts.*.comments.*.author'));
        $this->assertEquals([4, 3, 2, null, null, 1], Arr::dataGet($array, 'posts.*.comments.*.likes'));
        $this->assertEquals([], Arr::dataGet($array, 'posts.*.users.*.name', 'irrelevant'));
        $this->assertEquals([], Arr::dataGet($array, 'posts.*.users.*.name'));
    }
}

trait SupportTestTraitOne
{
}

trait SupportTestTraitTwo
{
    use SupportTestTraitOne;
}

class SupportTestClassOne
{
    use SupportTestTraitTwo;
}

class SupportTestClassTwo extends SupportTestClassOne
{
}

class SupportTestArrayAccess implements \ArrayAccess
{
    protected $attributes = [];
    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->attributes[$offset];
    }
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
}
