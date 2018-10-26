<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Translation\Exception\LogicException;
use Viserio\Component\Translation\MessageCatalogue;

/**
 * @internal
 */
final class MessageCatalogueTest extends TestCase
{
    public function testGetLocale(): void
    {
        $catalogue = new MessageCatalogue('en');

        $this->assertEquals('en', $catalogue->getLocale());
    }

    public function testGetDomains(): void
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => [], 'domain2' => []]);

        $this->assertEquals(['domain1', 'domain2'], $catalogue->getDomains());
    }

    public function testGetAll(): void
    {
        $catalogue = new MessageCatalogue('en', $messages = [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        $this->assertEquals(['foo' => 'foo'], $catalogue->getAll('domain1'));
        $this->assertEquals([], $catalogue->getAll('domain88'));
        $this->assertEquals($messages, $catalogue->getAll());
    }

    public function testHas(): void
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        $this->assertTrue($catalogue->has('foo', 'domain1'));
        $this->assertFalse($catalogue->has('bar', 'domain1'));
        $this->assertFalse($catalogue->has('foo', 'domain88'));
    }

    public function testHasWithFallback(): void
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo' => 'bar', 'foo1' => 'foo1']]);
        $catalogue->addFallbackCatalogue($catalogue1);

        $this->assertTrue($catalogue->has('foo1', 'domain1'));
    }

    public function testDefines(): void
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        $this->assertTrue($catalogue->defines('foo', 'domain1'));
    }

    public function testGetSet(): void
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

    public function testAdd(): void
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

    public function testRemove(): void
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'test'],
            'domain2' => ['bar' => 'bar'],
        ]);

        $this->assertEquals('test', $catalogue->get('foo', 'domain1'));

        $catalogue->remove('foo', 'domain1');

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
    }

    public function testReplace(): void
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue->replace($messages = ['foo1' => 'foo1'], 'domain1');

        $this->assertEquals($messages, $catalogue->getAll('domain1'));
    }

    public function testAddCatalogue(): void
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

    public function testAddFallbackCatalogue(): void
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

    public function testAddFallbackCatalogueWithCircularReference(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Circular reference detected when adding a fallback catalogue for locale [fr_FR].');

        $main     = new MessageCatalogue('en_US');
        $fallback = new MessageCatalogue('fr_FR');
        $fallback->addFallbackCatalogue($main);
        $main->addFallbackCatalogue($fallback);
    }

    public function testAddCatalogueWhenLocaleIsNotTheSameAsTheCurrentOne(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot add a catalogue for locale [fr] as the current locale for this catalogue is [en].');

        $catalogue = new MessageCatalogue('en');
        $catalogue->addCatalogue(new MessageCatalogue('fr', []));
    }

    /**
     * @dataProvider getValidLocalesTests
     *
     * @param mixed $locale
     */
    public function testSetValidLocale($locale): void
    {
        $message = new MessageCatalogue($locale);

        $this->assertEquals($locale, $message->getLocale());
    }

    /**
     * @return array
     */
    public function getValidLocalesTests(): array
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
