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

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\Translator;

/**
 * @internal
 *
 * @small
 */
final class TranslatorTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Translation\Translator */
    private $translator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $catalogue = new MessageCatalogue('en', [
            'messages' => [
                'foo' => 'bar',
            ],
        ]);

        $catalogue->addFallbackCatalogue(new MessageCatalogue('fr', [
            'messages' => [
                'test' => 'bar',
            ],
        ]));

        $this->translator = new Translator(
            $catalogue,
            new IntlMessageFormatter()
        );
    }

    public function testTrans(): void
    {
        self::assertSame('bar', $this->translator->trans('foo'));

        self::assertSame(
            [
                [
                    'locale' => 'en',
                    'domain' => 'messages',
                    'id' => 'foo',
                    'translation' => 'bar',
                    'parameters' => [],
                    'state' => 0,
                ],
            ],
            $this->translator->getCollectedMessages()
        );

        self::assertSame(
            'She avoids bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'female'])
        );

        self::assertSame(
            'They avoid bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'other'])
        );
    }

    public function testTransWithDomain(): void
    {
        self::assertSame('foo', $this->translator->trans('foo', [], 'admin'));
    }

    public function testTransWithVars(): void
    {
        self::assertSame('Hallo Daniel', $this->translator->trans('Hallo {name}', ['name' => 'Daniel']));
    }

    public function testSetAndGetLogger(): void
    {
        $logger = \Mockery::mock(LoggerInterface::class);
        $logger
            ->shouldReceive('debug')
            ->twice();
        $logger
            ->shouldReceive('warning')
            ->twice();
        $this->translator->setLogger($logger);

        self::assertSame('bar', $this->translator->trans('test'));
        self::assertSame('dont', $this->translator->trans('dont'));

        self::assertSame(
            'They avoid bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'other'])
        );

        self::assertSame('bar', $this->translator->trans('foo'));

        $this->translator->getCatalogue()->getFallbackCatalogue()->addFallbackCatalogue(new MessageCatalogue('de', [
            'messages' => [
                'wurst' => 'salat',
            ],
        ]));

        self::assertSame('salat', $this->translator->trans('wurst'));
    }

    public function testTranslateAddHelper(): void
    {
        $this->translator->addHelper('firstUpper', static function ($translation) {
            return \ucfirst($translation);
        });
        $this->translator->addHelper('truncate', static function ($translation, $length) {
            return \substr($translation, 0, (int) $length);
        });

        self::assertSame('He', $this->translator->trans('hello[truncate:2|firstUpper]'));
        self::assertSame('hello[nohelper]', $this->translator->trans('hello[nohelper]'));
        self::assertSame(
            'Tr',
            $this->translator->trans(
                'trainers: { count, number }[truncate:2|firstUpper]',
                [21629693]
            )
        );
    }

    public function testTranslateAddFilter(): void
    {
        $this->translator->addFilter(static function ($message) {
            return \strrev($message);
        });

        self::assertSame('olleh', $this->translator->trans('hello'));
    }
}
