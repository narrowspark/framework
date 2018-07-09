<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Str;

/**
 * @internal
 */
final class StrTest extends TestCase
{
    public function testStringCanBeLimitedByWords(): void
    {
        static::assertEquals('Narrowspark...', Str::words('Narrowspark Viserio', 1));
        static::assertEquals('Narrowspark___', Str::words('Narrowspark Viserio', 1, '___'));
        static::assertEquals('Narrowspark Viserio', Str::words('Narrowspark Viserio', 3));
    }

    public function testStringWithoutWordsDoesntProduceError(): void
    {
        $nbsp = \chr(0xC2) . \chr(0xA0);
        static::assertEquals(' ', Str::words(' '));
        static::assertEquals($nbsp, Str::words($nbsp));
    }

    public function testStringTrimmedOnlyWhereNecessary(): void
    {
        static::assertEquals(' Narrowspark Viserio ', Str::words(' Narrowspark Viserio ', 3));
        static::assertEquals(' Narrowspark...', Str::words(' Narrowspark Viserio ', 1));
    }

    public function testParseCallback(): void
    {
        static::assertEquals(['Class', 'method'], Str::parseCallback('Class@method', 'foo'));
        static::assertEquals(['Class', 'foo'], Str::parseCallback('Class', 'foo'));
    }

    public function testStrFinish(): void
    {
        static::assertEquals('test/string/', Str::finish('test/string', '/'));
        static::assertEquals('test/string/', Str::finish('test/string/', '/'));
        static::assertEquals('test/string/', Str::finish('test/string//', '/'));
    }

    public function testStrLimit(): void
    {
        $string = 'Narrowspark Framework for Creative People.';

        static::assertEquals('Narrows...', Str::limit($string, 7));
        static::assertEquals('Narrows', Str::limit($string, 7, ''));
        static::assertEquals('Narrowspark Framework for Creative People.', Str::limit($string, 100));
        static::assertEquals('Narrowspark...', Str::limit('Narrowspark Framework for Creative People.', 11));
        static::assertEquals('这是一...', Str::limit('这是一段中文', 6));
    }

    public function testRandom(): void
    {
        static::assertEquals(64, \mb_strlen(Str::random()));
        $randomInteger = \random_int(1, 100);
        static::assertEquals($randomInteger, \mb_strlen(Str::random($randomInteger)));
        static::assertInternalType('string', Str::random());

        $result = Str::random(20);
        static::assertInternalType('string', $result);
        static::assertEquals(20, \mb_strlen($result));
    }

    public function testSubstr(): void
    {
        static::assertEquals('Ё', Str::substr('БГДЖИЛЁ', -1));
        static::assertEquals('ЛЁ', Str::substr('БГДЖИЛЁ', -2));
        static::assertEquals('И', Str::substr('БГДЖИЛЁ', -3, 1));
        static::assertEquals('ДЖИЛ', Str::substr('БГДЖИЛЁ', 2, -1));
        static::assertEmpty(Str::substr('БГДЖИЛЁ', 4, -4));
        static::assertEquals('ИЛ', Str::substr('БГДЖИЛЁ', -3, -1));
        static::assertEquals('ГДЖИЛЁ', Str::substr('БГДЖИЛЁ', 1));
        static::assertEquals('ГДЖ', Str::substr('БГДЖИЛЁ', 1, 3));
        static::assertEquals('БГДЖ', Str::substr('БГДЖИЛЁ', 0, 4));
        static::assertEquals('Ё', Str::substr('БГДЖИЛЁ', -1, 1));
        static::assertEmpty(Str::substr('Б', 2));
    }

    public function testSnakeCase(): void
    {
        static::assertEquals('narrowspark_p_h_p_framework', Str::snake('NarrowsparkPHPFramework'));
        static::assertEquals('narrowspark_php_framework', Str::snake('NarrowsparkPhpFramework'));

        // snake cased strings should not contain spaces
        static::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark Php Framework'));
        static::assertEquals('narrowspark_php_framework', Str::snake('narrowspark php framework'));
        static::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark  Php  Framework'));

        // test cache
        static::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark  Php  Framework'));

        // `Str::snake()` should not duplicate the delimeters
        static::assertEquals('narrowspark_php_framework', Str::snake('narrowspark_php_framework'));
        static::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark_Php_Framework'));
        static::assertEquals('narrowspark-php-framework', Str::snake('Narrowspark_Php_Framework', '-'));
        static::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark_ _Php_ _Framework'));
        static::assertEquals('narrowspark_php_framework', Str::snake('Narrowspark     Php    Framework'));
        static::assertEquals('narrowspaaaark_phppp_framewoooork!!!', Str::snake('Narrowspaaaark Phppp Framewoooork!!!'));
        static::assertEquals('narrowspark_php_framework', Str::snake('NarrowsparkPhp_Framework'));

        static::assertEquals('foo_bar', Str::snake('Foo Bar'));
        static::assertEquals('foo_bar', Str::snake('foo bar'));
        static::assertEquals('foo_bar', Str::snake('FooBar'));
        static::assertEquals('foo_bar', Str::snake('fooBar'));
        static::assertEquals('foo_bar', Str::snake('foo-bar'));
        static::assertEquals('foo_bar', Str::snake('foo_bar'));
        static::assertEquals('foo_bar', Str::snake('FOO_BAR'));
        static::assertEquals('foo_bar', Str::snake('fooBar'));
        static::assertEquals('foo_bar', Str::snake('fooBar')); // test cache
    }

    public function testKebabCase(): void
    {
        static::assertEquals('foo-bar', Str::kebab('Foo Bar'));
        static::assertEquals('foo-bar', Str::kebab('foo bar'));
        static::assertEquals('foo-bar', Str::kebab('FooBar'));
        static::assertEquals('foo-bar', Str::kebab('fooBar'));
        static::assertEquals('foo-bar', Str::kebab('foo-bar'));
        static::assertEquals('foo-bar', Str::kebab('foo_bar'));
        static::assertEquals('foo-bar', Str::kebab('FOO_BAR'));
        static::assertEquals('foo-bar', Str::kebab('fooBar'));
        static::assertEquals('foo-bar', Str::kebab('fooBar')); // test cache
    }

    public function testStudlyCase(): void
    {
        //StudlyCase <=> PascalCase
        static::assertEquals('FooBar', Str::studly('Foo Bar'));
        static::assertEquals('FooBar', Str::studly('foo bar'));
        static::assertEquals('FooBar', Str::studly('FooBar'));
        static::assertEquals('FooBar', Str::studly('fooBar'));
        static::assertEquals('FooBar', Str::studly('foo-bar'));
        static::assertEquals('FooBar', Str::studly('foo_bar'));
        static::assertEquals('FooBar', Str::studly('FOO_BAR'));
        static::assertEquals('FooBar', Str::studly('foo_bar'));
        static::assertEquals('FooBar', Str::studly('foo_bar')); // test cache
        static::assertEquals('FooBarBaz', Str::studly('foo-barBaz'));
        static::assertEquals('FooBarBaz', Str::studly('foo-bar_baz'));
    }

    public function testReplaceFirst(): void
    {
        static::assertEquals('fooqux foobar', Str::replaceFirst('bar', 'qux', 'foobar foobar'));
        static::assertEquals('foo/qux? foo/bar?', Str::replaceFirst('bar?', 'qux?', 'foo/bar? foo/bar?'));
        static::assertEquals('foo foobar', Str::replaceFirst('bar', '', 'foobar foobar'));
        static::assertEquals('foobar foobar', Str::replaceFirst('xxx', 'yyy', 'foobar foobar'));
        static::assertEquals('foobar foobar', Str::replaceFirst('', 'yyy', 'foobar foobar'));
    }

    public function testReplaceLast(): void
    {
        static::assertEquals('foobar fooqux', Str::replaceLast('bar', 'qux', 'foobar foobar'));
        static::assertEquals('foo/bar? foo/qux?', Str::replaceLast('bar?', 'qux?', 'foo/bar? foo/bar?'));
        static::assertEquals('foobar foo', Str::replaceLast('bar', '', 'foobar foobar'));
        static::assertEquals('foobar foobar', Str::replaceLast('xxx', 'yyy', 'foobar foobar'));
        static::assertEquals('foobar foobar', Str::replaceLast('', 'yyy', 'foobar foobar'));
    }
}
