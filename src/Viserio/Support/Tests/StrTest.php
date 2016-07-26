<?php
declare(strict_types=1);
namespace Viserio\Support\Tests;

use Viserio\Support\Str;

class StrTest extends \PHPUnit_Framework_TestCase
{
    public function testStringCanBeLimitedByWords()
    {
        $this->assertEquals('Narrowspark...', Str::words('Narrowspark Viserio', 1));
        $this->assertEquals('Narrowspark___', Str::words('Narrowspark Viserio', 1, '___'));
        $this->assertEquals('Narrowspark Viserio', Str::words('Narrowspark Viserio', 3));
    }

    public function testStringWithoutWordsDoesntProduceError()
    {
        $nbsp = chr(0xC2) . chr(0xA0);
        $this->assertEquals(' ', Str::words(' '));
        $this->assertEquals($nbsp, Str::words($nbsp));
    }

    public function testStringTrimmedOnlyWhereNecessary()
    {
        $this->assertEquals(' Narrowspark Viserio ', Str::words(' Narrowspark Viserio ', 3));
        $this->assertEquals(' Narrowspark...', Str::words(' Narrowspark Viserio ', 1));
    }

    public function testParseCallback()
    {
        $this->assertEquals(['Class', 'method'], Str::parseCallback('Class@method', 'foo'));
        $this->assertEquals(['Class', 'foo'], Str::parseCallback('Class', 'foo'));
    }

    public function testStrFinish()
    {
        $this->assertEquals('test/string/', Str::finish('test/string', '/'));
        $this->assertEquals('test/string/', Str::finish('test/string/', '/'));
        $this->assertEquals('test/string/', Str::finish('test/string//', '/'));
    }

    public function testStrLimit()
    {
        $string = 'Narrowspark Framework for Creative People.';

        $this->assertEquals('Narrows...', Str::limit($string, 7));
        $this->assertEquals('Narrows', Str::limit($string, 7, ''));
        $this->assertEquals('Narrowspark Framework for Creative People.', Str::limit($string, 100));
        $this->assertEquals('Narrowspark...', Str::limit('Narrowspark Framework for Creative People.', 11));
        $this->assertEquals('这是一...', Str::limit('这是一段中文', 6));
    }

    public function testStudlyCase()
    {
        $this->assertEquals('FooBar', Str::studly('fooBar'));
        $this->assertEquals('FooBar', Str::studly('foo_bar'));
        $this->assertEquals('FooBar', Str::studly('foo_bar')); // test cache
        $this->assertEquals('FooBarBaz', Str::studly('foo-barBaz'));
        $this->assertEquals('FooBarBaz', Str::studly('foo-bar_baz'));
    }

    public function testRandom()
    {
        $this->assertEquals(64, strlen(Str::random()));
        $randomInteger = mt_rand(1, 100);
        $this->assertEquals($randomInteger, strlen(Str::random($randomInteger)));
        $this->assertInternalType('string', Str::random());

        $result = Str::random(20);
        $this->assertTrue(is_string($result));
        $this->assertEquals(20, strlen($result));
    }

    public function testSubstr()
    {
        $this->assertEquals('Ё', Str::substr('БГДЖИЛЁ', -1));
        $this->assertEquals('ЛЁ', Str::substr('БГДЖИЛЁ', -2));
        $this->assertEquals('И', Str::substr('БГДЖИЛЁ', -3, 1));
        $this->assertEquals('ДЖИЛ', Str::substr('БГДЖИЛЁ', 2, -1));
        $this->assertEmpty(Str::substr('БГДЖИЛЁ', 4, -4));
        $this->assertEquals('ИЛ', Str::substr('БГДЖИЛЁ', -3, -1));
        $this->assertEquals('ГДЖИЛЁ', Str::substr('БГДЖИЛЁ', 1));
        $this->assertEquals('ГДЖ', Str::substr('БГДЖИЛЁ', 1, 3));
        $this->assertEquals('БГДЖ', Str::substr('БГДЖИЛЁ', 0, 4));
        $this->assertEquals('Ё', Str::substr('БГДЖИЛЁ', -1, 1));
        $this->assertEmpty(Str::substr('Б', 2));
    }

    public function testSnake()
    {
        $this->assertEquals('narrowspark_p_h_p_framework', Str::snake('NarrowsparkPHPFramework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('NarrowsparkPhpFramework'));

        // snake cased strings should not contain spaces
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark Php Framework'));
        $this->assertEquals('narrowsparkphpframework', Str::snake('narrowspark php framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark  Php  Framework'));

        // test cache
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark  Php  Framework'));

        // `Str::snake()` should not duplicate the delimeters
        $this->assertEquals('narrowspark_php_framework', Str::snake('narrowspark_php_framework'));
        $this->assertEquals('narrowspark__php__framework', Str::snake('Narrowspark_Php_Framework'));
        $this->assertEquals('narrowspark_-php_-framework', Str::snake('Narrowspark_Php_Framework', '-'));
        $this->assertEquals('narrowspark___php___framework', Str::snake('Narrowspark_ _Php_ _Framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark     Php    Framework'));
        $this->assertEquals('narrowspaaaark_phppp_framewoooork!!!', Str::snake('Narrowspaaaark Phppp Framewoooork!!!'));
        $this->assertEquals('narrowspark_php__framework', Str::snake('NarrowsparkPhp_Framework'));
    }
}
