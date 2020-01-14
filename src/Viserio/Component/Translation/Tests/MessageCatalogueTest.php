<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Translation\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Contract\Translation\Exception\LogicException;

/**
 * @internal
 *
 * @small
 */
final class MessageCatalogueTest extends TestCase
{
    public function testGetLocale(): void
    {
        $catalogue = new MessageCatalogue('en');

        self::assertEquals('en', $catalogue->getLocale());
    }

    public function testGetDomains(): void
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => [], 'domain2' => []]);

        self::assertEquals(['domain1', 'domain2'], $catalogue->getDomains());
    }

    public function testGetAll(): void
    {
        $catalogue = new MessageCatalogue('en', $messages = [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        self::assertEquals(['foo' => 'foo'], $catalogue->getAll('domain1'));
        self::assertEquals([], $catalogue->getAll('domain88'));
        self::assertEquals($messages, $catalogue->getAll());
    }

    public function testHas(): void
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        self::assertTrue($catalogue->has('foo', 'domain1'));
        self::assertFalse($catalogue->has('bar', 'domain1'));
        self::assertFalse($catalogue->has('foo', 'domain88'));
    }

    public function testHasWithFallback(): void
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo' => 'bar', 'foo1' => 'foo1']]);
        $catalogue->addFallbackCatalogue($catalogue1);

        self::assertTrue($catalogue->has('foo1', 'domain1'));
    }

    public function testDefines(): void
    {
        $catalogue = new MessageCatalogue('en_US', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);

        self::assertTrue($catalogue->defines('foo', 'domain1'));
    }

    public function testGetSet(): void
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

    public function testAdd(): void
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

    public function testRemove(): void
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'test'],
            'domain2' => ['bar' => 'bar'],
        ]);

        self::assertEquals('test', $catalogue->get('foo', 'domain1'));

        $catalogue->remove('foo', 'domain1');

        self::assertEquals('foo', $catalogue->get('foo', 'domain1'));
    }

    public function testReplace(): void
    {
        $catalogue = new MessageCatalogue('en', [
            'domain1' => ['foo' => 'foo'],
            'domain2' => ['bar' => 'bar'],
        ]);
        $catalogue->replace($messages = ['foo1' => 'foo1'], 'domain1');

        self::assertEquals($messages, $catalogue->getAll('domain1'));
    }

    public function testAddCatalogue(): void
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

    public function testAddFallbackCatalogue(): void
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

    public function testAddFallbackCatalogueWithCircularReference(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Circular reference detected when adding a fallback catalogue for locale [fr_FR].');

        $main = new MessageCatalogue('en_US');
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
     * @dataProvider provideSetValidLocaleCases
     *
     * @param mixed $locale
     */
    public function testSetValidLocale($locale): void
    {
        $message = new MessageCatalogue($locale);

        self::assertEquals($locale, $message->getLocale());
    }

    public static function provideSetValidLocaleCases(): iterable
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
