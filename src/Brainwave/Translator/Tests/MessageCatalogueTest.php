<?php
namespace Brainwave\Test\Translator;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.6-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Translator\MessageCatalogue;

/**
 * MessageCatalogueTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class MessageCatalogueTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLocale()
    {
        $catalogue = new MessageCatalogue('en');
        $this->assertEquals('en', $catalogue->getLocale());
    }

    public function testGetDomains()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => [], 'domain2' => []]);
        $this->assertEquals(['domain1', 'domain2'], $catalogue->getDomains());
    }

    public function testAll()
    {
        $catalogue = new MessageCatalogue('en', $messages = ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $this->assertEquals(['foo' => 'foo'], $catalogue->all('domain1'));
        $this->assertEquals([], $catalogue->all('domain88'));
        $this->assertEquals($messages, $catalogue->all());
    }

    public function testHas()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $this->assertTrue($catalogue->has('foo', 'domain1'));
        $this->assertFalse($catalogue->has('bar', 'domain1'));
        $this->assertFalse($catalogue->has('foo', 'domain88'));
    }

    public function testGetSet()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue->set('foo1', 'foo1', 'domain1');
        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
    }

    public function testAdd()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue->add(['foo1' => 'foo1'], 'domain1');
        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
        $catalogue->add(['foo' => 'bar'], 'domain1');
        $this->assertEquals('bar', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
        $catalogue->add(['foo' => 'bar'], 'domain88');
        $this->assertEquals('bar', $catalogue->get('foo', 'domain88'));
    }

    public function testReplace()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue->replace($messages = ['foo1' => 'foo1'], 'domain1');
        $this->assertEquals($messages, $catalogue->all('domain1'));
    }

    public function testAddCatalogue()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo1' => 'foo1']]);
        $catalogue->addCatalogue($catalogue1);
        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
    }

    public function testAddFallbackCatalogue()
    {
        $catalogue = new MessageCatalogue('en_US', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo' => 'bar', 'foo1' => 'foo1']]);
        $catalogue->addFallbackCatalogue($catalogue1);
        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddFallbackCatalogueWithCircularReference()
    {
        $main = new MessageCatalogue('en_US');
        $fallback = new MessageCatalogue('fr_FR');
        $fallback->addFallbackCatalogue($main);
        $main->addFallbackCatalogue($fallback);
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddCatalogueWhenLocaleIsNotTheSameAsTheCurrentOne()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->addCatalogue(new MessageCatalogue('fr', []));
    }
}
