<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Str;

class StrTest extends TestCase
{
    public function testStringCanBeLimitedByWords(): void
    {
        self::assertEquals('Narrowspark...', Str::words('Narrowspark Viserio', 1));
        self::assertEquals('Narrowspark___', Str::words('Narrowspark Viserio', 1, '___'));
        self::assertEquals('Narrowspark Viserio', Str::words('Narrowspark Viserio', 3));
    }

    public function testStringWithoutWordsDoesntProduceError(): void
    {
        $nbsp = \chr(0xC2) . \chr(0xA0);
        self::assertEquals(' ', Str::words(' '));
        self::assertEquals($nbsp, Str::words($nbsp));
    }

    public function testStringTrimmedOnlyWhereNecessary(): void
    {
        self::assertEquals(' Narrowspark Viserio ', Str::words(' Narrowspark Viserio ', 3));
        self::assertEquals(' Narrowspark...', Str::words(' Narrowspark Viserio ', 1));
    }

    public function testParseCallback(): void
    {
        self::assertEquals(['Class', 'method'], Str::parseCallback('Class@method', 'foo'));
        self::assertEquals(['Class', 'foo'], Str::parseCallback('Class', 'foo'));
    }

    public function testStrFinish(): void
    {
        self::assertEquals('test/string/', Str::finish('test/string', '/'));
        self::assertEquals('test/string/', Str::finish('test/string/', '/'));
        self::assertEquals('test/string/', Str::finish('test/string//', '/'));
    }

    public function testStrLimit(): void
    {
        $string = 'Narrowspark Framework for Creative People.';

        self::assertEquals('Narrows...', Str::limit($string, 7));
        self::assertEquals('Narrows', Str::limit($string, 7, ''));
        self::assertEquals('Narrowspark Framework for Creative People.', Str::limit($string, 100));
        self::assertEquals('Narrowspark...', Str::limit('Narrowspark Framework for Creative People.', 11));
        self::assertEquals('这是一...', Str::limit('这是一段中文', 6));
    }

    public function testRandom(): void
    {
        self::assertEquals(64, \mb_strlen(Str::random()));
        $randomInteger = \random_int(1, 100);
        self::assertEquals($randomInteger, \mb_strlen(Str::random($randomInteger)));
        self::assertInternalType('string', Str::random());

        $result = Str::random(20);
        self::assertTrue(\is_string($result));
        self::assertEquals(20, \mb_strlen($result));
    }

    public function testSubstr(): void
    {
        self::assertEquals('Ё', Str::substr('БГДЖИЛЁ', -1));
        self::assertEquals('ЛЁ', Str::substr('БГДЖИЛЁ', -2));
        self::assertEquals('И', Str::substr('БГДЖИЛЁ', -3, 1));
        self::assertEquals('ДЖИЛ', Str::substr('БГДЖИЛЁ', 2, -1));
        self::assertEmpty(Str::substr('БГДЖИЛЁ', 4, -4));
        self::assertEquals('ИЛ', Str::substr('БГДЖИЛЁ', -3, -1));
        self::assertEquals('ГДЖИЛЁ', Str::substr('БГДЖИЛЁ', 1));
        self::assertEquals('ГДЖ', Str::substr('БГДЖИЛЁ', 1, 3));
        self::assertEquals('БГДЖ', Str::substr('БГДЖИЛЁ', 0, 4));
        self::assertEquals('Ё', Str::substr('БГДЖИЛЁ', -1, 1));
        self::assertEmpty(Str::substr('Б', 2));
    }

    public function testSnakeCase(): void
    {
        self::assertEquals('narrowspark_p_h_p_framework', Str::snake('NarrowsparkPHPFramework'));
        self::assertEquals('narrowspark_php_framework', Str::snake('NarrowsparkPhpFramework'));

        // snake cased strings should not contain spaces
        self::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark Php Framework'));
        self::assertEquals('narrowspark_php_framework', Str::snake('narrowspark php framework'));
        self::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark  Php  Framework'));

        // test cache
        self::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark  Php  Framework'));

        // `Str::snake()` should not duplicate the delimeters
        self::assertEquals('narrowspark_php_framework', Str::snake('narrowspark_php_framework'));
        self::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark_Php_Framework'));
        self::assertEquals('narrowspark-php-framework', Str::snake('Narrowspark_Php_Framework', '-'));
        self::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark_ _Php_ _Framework'));
        self::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark     Php    Framework'));
        self::assertEquals('narrowspaaaark_phppp_framewoooork!!!', Str::snake('Narrowspaaaark Phppp Framewoooork!!!'));
        self::assertEquals('narrowspark_php_framework', Str::snake('NarrowsparkPhp_Framework'));

        self::assertEquals('foo_bar', Str::snake('Foo Bar'));
        self::assertEquals('foo_bar', Str::snake('foo bar'));
        self::assertEquals('foo_bar', Str::snake('FooBar'));
        self::assertEquals('foo_bar', Str::snake('fooBar'));
        self::assertEquals('foo_bar', Str::snake('foo-bar'));
        self::assertEquals('foo_bar', Str::snake('foo_bar'));
        self::assertEquals('foo_bar', Str::snake('FOO_BAR'));
        self::assertEquals('foo_bar', Str::snake('fooBar'));
        self::assertEquals('foo_bar', Str::snake('fooBar')); // test cache
    }

    public function testKebabCase(): void
    {
        self::assertEquals('foo-bar', Str::kebab('Foo Bar'));
        self::assertEquals('foo-bar', Str::kebab('foo bar'));
        self::assertEquals('foo-bar', Str::kebab('FooBar'));
        self::assertEquals('foo-bar', Str::kebab('fooBar'));
        self::assertEquals('foo-bar', Str::kebab('foo-bar'));
        self::assertEquals('foo-bar', Str::kebab('foo_bar'));
        self::assertEquals('foo-bar', Str::kebab('FOO_BAR'));
        self::assertEquals('foo-bar', Str::kebab('fooBar'));
        self::assertEquals('foo-bar', Str::kebab('fooBar')); // test cache
    }

    public function testStudlyCase(): void
    {
        //StudlyCase <=> PascalCase
        self::assertEquals('FooBar', Str::studly('Foo Bar'));
        self::assertEquals('FooBar', Str::studly('foo bar'));
        self::assertEquals('FooBar', Str::studly('FooBar'));
        self::assertEquals('FooBar', Str::studly('fooBar'));
        self::assertEquals('FooBar', Str::studly('foo-bar'));
        self::assertEquals('FooBar', Str::studly('foo_bar'));
        self::assertEquals('FooBar', Str::studly('FOO_BAR'));
        self::assertEquals('FooBar', Str::studly('foo_bar'));
        self::assertEquals('FooBar', Str::studly('foo_bar')); // test cache
        self::assertEquals('FooBarBaz', Str::studly('foo-barBaz'));
        self::assertEquals('FooBarBaz', Str::studly('foo-bar_baz'));
    }

    public function testReplaceFirst(): void
    {
        self::assertEquals('fooqux foobar', Str::replaceFirst('bar', 'qux', 'foobar foobar'));
        self::assertEquals('foo/qux? foo/bar?', Str::replaceFirst('bar?', 'qux?', 'foo/bar? foo/bar?'));
        self::assertEquals('foo foobar', Str::replaceFirst('bar', '', 'foobar foobar'));
        self::assertEquals('foobar foobar', Str::replaceFirst('xxx', 'yyy', 'foobar foobar'));
        self::assertEquals('foobar foobar', Str::replaceFirst('', 'yyy', 'foobar foobar'));
    }

    public function testReplaceLast(): void
    {
        self::assertEquals('foobar fooqux', Str::replaceLast('bar', 'qux', 'foobar foobar'));
        self::assertEquals('foo/bar? foo/qux?', Str::replaceLast('bar?', 'qux?', 'foo/bar? foo/bar?'));
        self::assertEquals('foobar foo', Str::replaceLast('bar', '', 'foobar foobar'));
        self::assertEquals('foobar foobar', Str::replaceLast('xxx', 'yyy', 'foobar foobar'));
        self::assertEquals('foobar foobar', Str::replaceLast('', 'yyy', 'foobar foobar'));
    }
}
