<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests;

use Viserio\Translation\MessageCatalogue;

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
        $catalogue = new MessageCatalogue('en', $messages = [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        $this->assertEquals(['foo' => 'foo'], $catalogue->all('domain1'));
        $this->assertEquals([], $catalogue->all('domain88'));
        $this->assertEquals($messages, $catalogue->all());
    }

    public function testHas()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        $this->assertTrue($catalogue->has('foo', 'domain1'));
        $this->assertFalse($catalogue->has('bar', 'domain1'));
        $this->assertFalse($catalogue->has('foo', 'domain88'));
    }

    public function testHasWithFallback()
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo' => 'bar', 'foo1' => 'foo1']]);
        $catalogue->addFallbackCatalogue($catalogue1);

        $this->assertTrue($catalogue->has('foo1', 'domain1'));
    }

    public function testDefines($value='')
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        $this->assertTrue($catalogue->defines('foo', 'domain1'));
    }

    public function testGetSet()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue->set('foo1', 'foo1', 'domain1');

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
        $this->assertEquals('id', $catalogue->get('id', 'domain'));
    }

    public function testAdd()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue->add(['foo1' => 'foo1'], 'domain1');

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        $catalogue->add(['foo' => 'bar'], 'domain1');

        $this->assertEquals('bar', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        $catalogue->add(['foo' => 'bar'], 'domain88');

        $this->assertEquals('bar', $catalogue->get('foo', 'domain88'));
    }

    public function testRemove()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'test'],
            'domain2' => ['bar' => 'bar'],
        ]);

        $this->assertEquals('test', $catalogue->get('foo', 'domain1'));

        $catalogue->remove('foo', 'domain1');

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
    }

    public function testReplace()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue->replace($messages = ['foo1' => 'foo1'], 'domain1');

        $this->assertEquals($messages, $catalogue->all('domain1'));
    }

    public function testAddCatalogue()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo1' => 'foo1']]);
        $catalogue->addCatalogue($catalogue1);

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
    }

    public function testAddFallbackCatalogue()
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo' => 'bar', 'foo1' => 'foo1']]);
        $catalogue->addFallbackCatalogue($catalogue1);

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        $this->assertInstanceOf(MessageCatalogue::class, $catalogue->getFallbackCatalogue());
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

    /**
     * @dataProvider getValidLocalesTests
     */
    public function testSetValidLocale($locale)
    {
        $message = new MessageCatalogue($locale);

        $this->assertEquals($locale, $message->getLocale());
    }

    public function getValidLocalesTests()
    {
        return [
            [''],
            ['fr'],
            ['francais'],
            ['FR'],
            ['frFR'],
            ['fr-FR'],
            ['fr_FR'],
            ['fr.FR'],
            ['fr-FR.UTF8'],
            ['sr@latin'],
        ];
    }
}
