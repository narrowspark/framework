<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\MessageCatalogue;

class MessageCatalogueTest extends TestCase
{
    public function testGetLocale()
    {
        $catalogue = new MessageCatalogue('en');

        self::assertEquals('en', $catalogue->getLocale());
    }

    public function testGetDomains()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => [], 'domain2' => []]);

        self::assertEquals(['domain1', 'domain2'], $catalogue->getDomains());
    }

    public function testGetAll()
    {
        $catalogue = new MessageCatalogue('en', $messages = [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        self::assertEquals(['foo' => 'foo'], $catalogue->getAll('domain1'));
        self::assertEquals([], $catalogue->getAll('domain88'));
        self::assertEquals($messages, $catalogue->getAll());
    }

    public function testHas()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        self::assertTrue($catalogue->has('foo', 'domain1'));
        self::assertFalse($catalogue->has('bar', 'domain1'));
        self::assertFalse($catalogue->has('foo', 'domain88'));
    }

    public function testHasWithFallback()
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo' => 'bar', 'foo1' => 'foo1']]);
        $catalogue->addFallbackCatalogue($catalogue1);

        self::assertTrue($catalogue->has('foo1', 'domain1'));
    }

    public function testDefines($value = '')
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        self::assertTrue($catalogue->defines('foo', 'domain1'));
    }

    public function testGetSet()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue->set('foo1', 'foo1', 'domain1');

        self::assertEquals('foo', $catalogue->get('foo', 'domain1'));
        self::assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
        self::assertEquals('id', $catalogue->get('id', 'domain'));
    }

    public function testAdd()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue->add(['foo1' => 'foo1'], 'domain1');

        self::assertEquals('foo', $catalogue->get('foo', 'domain1'));
        self::assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        $catalogue->add(['foo' => 'bar'], 'domain1');

        self::assertEquals('bar', $catalogue->get('foo', 'domain1'));
        self::assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        $catalogue->add(['foo' => 'bar'], 'domain88');

        self::assertEquals('bar', $catalogue->get('foo', 'domain88'));
    }

    public function testRemove()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'test'],
            'domain2' => ['bar' => 'bar'],
        ]);

        self::assertEquals('test', $catalogue->get('foo', 'domain1'));

        $catalogue->remove('foo', 'domain1');

        self::assertEquals('foo', $catalogue->get('foo', 'domain1'));
    }

    public function testReplace()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue->replace($messages = ['foo1' => 'foo1'], 'domain1');

        self::assertEquals($messages, $catalogue->getAll('domain1'));
    }

    public function testAddCatalogue()
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo1' => 'foo1']]);
        $catalogue->addCatalogue($catalogue1);

        self::assertEquals('foo', $catalogue->get('foo', 'domain1'));
        self::assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
    }

    public function testAddFallbackCatalogue()
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo' => 'bar', 'foo1' => 'foo1']]);
        $catalogue->addFallbackCatalogue($catalogue1);

        self::assertEquals('foo', $catalogue->get('foo', 'domain1'));
        self::assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        self::assertInstanceOf(MessageCatalogue::class, $catalogue->getFallbackCatalogue());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Circular reference detected when adding a fallback catalogue for locale [fr_FR].
     */
    public function testAddFallbackCatalogueWithCircularReference()
    {
        $main     = new MessageCatalogue('en_US');
        $fallback = new MessageCatalogue('fr_FR');
        $fallback->addFallbackCatalogue($main);
        $main->addFallbackCatalogue($fallback);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot add a catalogue for locale [fr] as the current locale for this catalogue is [en].
     */
    public function testAddCatalogueWhenLocaleIsNotTheSameAsTheCurrentOne()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->addCatalogue(new MessageCatalogue('fr', []));
    }

    /**
     * @dataProvider getValidLocalesTests
     *
     * @param mixed $locale
     */
    public function testSetValidLocale($locale)
    {
        $message = new MessageCatalogue($locale);

        self::assertEquals($locale, $message->getLocale());
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
