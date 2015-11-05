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

use Brainwave\Support\Str;

/**
 * StrTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class StrTest extends \PHPUnit_Framework_TestCase
{
    public function testStringCanBeLimitedByWords()
    {
        $this->assertEquals('Narrowspark…', Str::words('Narrowspark Brainwave', 1));
        $this->assertEquals('Narrowspark___', Str::words('Narrowspark Brainwave', 1, '___'));
        $this->assertEquals('Narrowspark Brainwave', Str::words('Narrowspark Brainwave', 3));
    }

    public function testStringTrimmedOnlyWhereNecessary()
    {
        $this->assertEquals(' Narrowspark Brainwave ', Str::words(' Narrowspark Brainwave ', 3));
        $this->assertEquals(' Narrowspark...', Str::words(' Narrowspark Brainwave ', 1));
    }

    public function testStringWithoutWordsDoesntProduceError()
    {
        $nbsp = chr(0xC2).chr(0xA0);
        $this->assertEquals(' ', Str::words(' '));
        $this->assertEquals($nbsp, Str::words($nbsp));
    }

    public function testParseCallback()
    {
        $this->assertEquals(['Class', 'method'], Str::parseCallback('Class@method', 'foo'));
        $this->assertEquals(['Class', 'foo'], Str::parseCallback('Class', 'foo'));
    }

    public function testStrRandom()
    {
        $result = Str::random(20);
        $this->assertTrue(is_string($result));
        $this->assertEquals(20, strlen($result));
    }

    public function testStrFinish()
    {
        $this->assertEquals('test/string/', Str::finish('test/string', '/'));
        $this->assertEquals('test/string/', Str::finish('test/string/', '/'));
        $this->assertEquals('test/string/', Str::finish('test/string//', '/'));
    }

    public function testSnakeCase()
    {
        $this->assertEquals('foo_bar', Str::snake('fooBar'));
        $this->assertEquals('foo_bar', Str::snake('fooBar')); // test cache
    }

    public function testStrLimit()
    {
        $string = 'The PHP framework for web artisans.';
        $this->assertEquals('The PHP...', Str::limit($string, 7));
        $this->assertEquals('The PHP', Str::limit($string, 7, ''));
        $this->assertEquals('The PHP framework for web artisans.', Str::limit($string, 100));
    }
    public function testStudlyCase()
    {
        $this->assertEquals('FooBar', Str::studly('fooBar'));
        $this->assertEquals('FooBar', Str::studly('foo_bar'));
        $this->assertEquals('FooBar', Str::studly('foo_bar')); // test cache
        $this->assertEquals('FooBarBaz', Str::studly('foo-barBaz'));
        $this->assertEquals('FooBarBaz', Str::studly('foo-bar_baz'));
    }

    public function testLimit()
    {
        $this->assertEquals('Narrowspark…', Str::limit('Narrowspark Framework for Creative People.', 11));
    }

    public function testQuickRandom()
    {
        $randomInteger = mt_rand(1, 100);
        $this->assertEquals($randomInteger, strlen(Str::quickRandom($randomInteger)));
        $this->assertInternalType('string', Str::quickRandom());
        $this->assertEquals(16, strlen(Str::quickRandom()));
    }

    public function testRandom()
    {
        $this->assertEquals(16, strlen(Str::random()));
        $randomInteger = mt_rand(1, 100);
        $this->assertEquals($randomInteger, strlen(Str::random($randomInteger)));
        $this->assertInternalType('string', Str::random());
    }

    public function testSnake()
    {
        $this->assertEquals('narrowspark_p_h_p_framework', Str::snake('NarrowsparkPHPFramework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('NarrowsparkPhpFramework'));

        // snake cased strings should not contain spaces
        $this->assertEquals('narrowspark_php_framework', Str::snake('narrowspark php framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark Php Framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark  Php  Framework'));

        // `Str::snake()` should not duplicate the delimeters
        $this->assertEquals('narrowspark_php_framework', Str::snake('narrowspark_php_framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark_Php_Framework'));
        $this->assertEquals('narrowspark_-php_-framework', Str::snake('Narrowspark_Php_Framework', '-'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark_ _Php_ _Framework'));
        $this->assertEquals('narrowspark_php_framework', Str::snake('Narrowspark     Php    Framework'));
        $this->assertEquals('narrowspaaaark_phppp_framewoooork!!!', Str::snake('Narrowspaaaark Phppp Framewoooork!!!'));
    }

    public function testBetween()
    {
        $this->assertEquals('Middle', Str::between('StartMiddleEnd', 'Start', 'End'));
        $this->assertEquals('', Str::between('MiddleEnd', 'Start', 'End'));
        $this->assertEquals('', Str::between('StartMiddle', 'Start', 'End'));
        $this->assertEquals('', Str::between('StartMiddleEnd', 'End', 'Start'));
        $this->assertEquals('Middle', Str::between('StartMiddleStart', 'Start', 'Start'));
        $this->assertEquals('MiddleEnd', Str::between('StartMiddleEnd', 'Start', ''));
        $this->assertEquals('StartMiddle', Str::between('StartMiddleEnd', '', 'End'));
        $this->assertEquals('StartMiddleEnd', Str::between('StartMiddleEnd', '', ''));
    }
}
