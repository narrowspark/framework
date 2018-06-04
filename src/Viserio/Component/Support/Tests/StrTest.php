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
        $this->assertEquals('Narrowspark...', Str::words('Narrowspark Viserio', 1));
        $this->assertEquals('Narrowspark___', Str::words('Narrowspark Viserio', 1, '___'));
        $this->assertEquals('Narrowspark Viserio', Str::words('Narrowspark Viserio', 3));
    }

    public function testStringWithoutWordsDoesntProduceError(): void
    {
        $nbsp = \chr(0xC2) . \chr(0xA0);
        $this->assertEquals(' ', Str::words(' '));
        $this->assertEquals($nbsp, Str::words($nbsp));
    }

    public function testStringTrimmedOnlyWhereNecessary(): void
    {
        $this->assertEquals(' Narrowspark Viserio ', Str::words(' Narrowspark Viserio ', 3));
        $this->assertEquals(' Narrowspark...', Str::words(' Narrowspark Viserio ', 1));
    }

    public function testParseCallback(): void
    {
        $this->assertEquals(['Class', 'method'], Str::parseCallback('Class@method', 'foo'));
        $this->assertEquals(['Class', 'foo'], Str::parseCallback('Class', 'foo'));
    }

    public function testStrFinish(): void
    {
        $this->assertEquals('test/string/', Str::finish('test/string', '/'));
        $this->assertEquals('test/string/', Str::finish('test/string/', '/'));
        $this->assertEquals('test/string/', Str::finish('test/string//', '/'));
    }

    public function testStrLimit(): void
    {
        $string = 'Narrowspark Framework for Creative People.';

        $this->assertEquals('Narrows...', Str::limit($string, 7));
        $this->assertEquals('Narrows', Str::limit($string, 7, ''));
        $this->assertEquals('Narrowspark Framework for Creative People.', Str::limit($string, 100));
        $this->assertEquals('Narrowspark...', Str::limit('Narrowspark Framework for Creative People.', 11));
        $this->assertEquals('这是一...', Str::limit('这是一段中文', 6));
    }

    public function testRandom(): void
    {
        $this->assertEquals(64, \mb_strlen(Str::random()));
        $randomInteger = \random_int(1, 100);
        $this->assertEquals($randomInteger, \mb_strlen(Str::random($randomInteger)));
        $this->assertInternalType('string', Str::random());

        $result = Str::random(20);
        $this->assertInternalType('string', $result);
        $this->assertEquals(20, \mb_strlen($result));
    }

    public function testSubstr(): void
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

    public function testSnakeCase(): void
    {
        $this->assertEquals('narrowspark_p_h_p_framework', Str::snake('NarrowsparkPHPFramework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('NarrowsparkPhpFramework'));

        // snake cased strings should not contain spaces
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark Php Framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('narrowspark php framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark  Php  Framework'));

        // test cache
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark  Php  Framework'));

        // `Str::snake()` should not duplicate the delimeters
        $this->assertEquals('narrowspark_php_framework', Str::snake('narrowspark_php_framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark_Php_Framework'));
        $this->assertEquals('narrowspark-php-framework', Str::snake('Narrowspark_Php_Framework', '-'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark_ _Php_ _Framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark     Php    Framework'));
        $this->assertEquals('narrowspaaaark_phppp_framewoooork!!!', Str::snake('Narrowspaaaark Phppp Framewoooork!!!'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('NarrowsparkPhp_Framework'));

        $this->assertEquals('foo_bar', Str::snake('Foo Bar'));
        $this->assertEquals('foo_bar', Str::snake('foo bar'));
        $this->assertEquals('foo_bar', Str::snake('FooBar'));
        $this->assertEquals('foo_bar', Str::snake('fooBar'));
        $this->assertEquals('foo_bar', Str::snake('foo-bar'));
        $this->assertEquals('foo_bar', Str::snake('foo_bar'));
        $this->assertEquals('foo_bar', Str::snake('FOO_BAR'));
        $this->assertEquals('foo_bar', Str::snake('fooBar'));
        $this->assertEquals('foo_bar', Str::snake('fooBar')); // test cache
    }

    public function testKebabCase(): void
    {
        $this->assertEquals('foo-bar', Str::kebab('Foo Bar'));
        $this->assertEquals('foo-bar', Str::kebab('foo bar'));
        $this->assertEquals('foo-bar', Str::kebab('FooBar'));
        $this->assertEquals('foo-bar', Str::kebab('fooBar'));
        $this->assertEquals('foo-bar', Str::kebab('foo-bar'));
        $this->assertEquals('foo-bar', Str::kebab('foo_bar'));
        $this->assertEquals('foo-bar', Str::kebab('FOO_BAR'));
        $this->assertEquals('foo-bar', Str::kebab('fooBar'));
        $this->assertEquals('foo-bar', Str::kebab('fooBar')); // test cache
    }

    public function testStudlyCase(): void
    {
        //StudlyCase <=> PascalCase
        $this->assertEquals('FooBar', Str::studly('Foo Bar'));
        $this->assertEquals('FooBar', Str::studly('foo bar'));
        $this->assertEquals('FooBar', Str::studly('FooBar'));
        $this->assertEquals('FooBar', Str::studly('fooBar'));
        $this->assertEquals('FooBar', Str::studly('foo-bar'));
        $this->assertEquals('FooBar', Str::studly('foo_bar'));
        $this->assertEquals('FooBar', Str::studly('FOO_BAR'));
        $this->assertEquals('FooBar', Str::studly('foo_bar'));
        $this->assertEquals('FooBar', Str::studly('foo_bar')); // test cache
        $this->assertEquals('FooBarBaz', Str::studly('foo-barBaz'));
        $this->assertEquals('FooBarBaz', Str::studly('foo-bar_baz'));
    }

    public function testReplaceFirst(): void
    {
        $this->assertEquals('fooqux foobar', Str::replaceFirst('bar', 'qux', 'foobar foobar'));
        $this->assertEquals('foo/qux? foo/bar?', Str::replaceFirst('bar?', 'qux?', 'foo/bar? foo/bar?'));
        $this->assertEquals('foo foobar', Str::replaceFirst('bar', '', 'foobar foobar'));
        $this->assertEquals('foobar foobar', Str::replaceFirst('xxx', 'yyy', 'foobar foobar'));
        $this->assertEquals('foobar foobar', Str::replaceFirst('', 'yyy', 'foobar foobar'));
    }

    public function testReplaceLast(): void
    {
        $this->assertEquals('foobar fooqux', Str::replaceLast('bar', 'qux', 'foobar foobar'));
        $this->assertEquals('foo/bar? foo/qux?', Str::replaceLast('bar?', 'qux?', 'foo/bar? foo/bar?'));
        $this->assertEquals('foobar foo', Str::replaceLast('bar', '', 'foobar foobar'));
        $this->assertEquals('foobar foobar', Str::replaceLast('xxx', 'yyy', 'foobar foobar'));
        $this->assertEquals('foobar foobar', Str::replaceLast('', 'yyy', 'foobar foobar'));
    }
}
