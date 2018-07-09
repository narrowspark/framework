<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Contract\Translation\MessageFormatter as MessageFormatterContract;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\Translator;

/**
 * @internal
 */
final class TranslatorTest extends MockeryTestCase
{
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

    public function testGetFormatterAndCatalogue(): void
    {
        static::assertInstanceOf(MessageCatalogue::class, $this->translator->getCatalogue());
        static::assertInstanceOf(MessageFormatterContract::class, $this->translator->getFormatter());
    }

    public function testTrans(): void
    {
        static::assertSame('bar', $this->translator->trans('foo'));

        static::assertSame(
            [
                [
                    'locale'      => 'en',
                    'domain'      => 'messages',
                    'id'          => 'foo',
                    'translation' => 'bar',
                    'parameters'  => [],
                    'state'       => 0,
                ],
            ],
            $this->translator->getCollectedMessages()
        );

        static::assertSame(
            'She avoids bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'female'])
        );

        static::assertSame(
            'They avoid bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'other'])
        );
    }

    public function testTransWithDomain(): void
    {
        static::assertSame('foo', $this->translator->trans('foo', [], 'admin'));
    }

    public function testTransWithVars(): void
    {
        static::assertSame('Hallo Daniel', $this->translator->trans('Hallo {name}', ['name' => 'Daniel']));
    }

    public function testSetAndGetLogger(): void
    {
        $logger = $this->mock(LoggerInterface::class);
        $logger
            ->shouldReceive('debug')
            ->twice();
        $logger
            ->shouldReceive('warning')
            ->twice();
        $this->translator->setLogger($logger);

        static::assertSame('bar', $this->translator->trans('test'));
        static::assertSame('dont', $this->translator->trans('dont'));

        static::assertSame(
            'They avoid bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'other'])
        );

        static::assertSame('bar', $this->translator->trans('foo'));

        $this->translator->getCatalogue()->getFallbackCatalogue()->addFallbackCatalogue(new MessageCatalogue('de', [
            'messages' => [
                'wurst' => 'salat',
            ],
        ]));

        static::assertSame('salat', $this->translator->trans('wurst'));
    }

    public function testTranslateAddHelper(): void
    {
        $this->translator->addHelper('firstUpper', function ($translation) {
            return \ucfirst($translation);
        });
        $this->translator->addHelper('truncate', function ($translation, $length) {
            return \mb_substr($translation, 0, (int) $length);
        });

        static::assertSame('He', $this->translator->trans('hello[truncate:2|firstUpper]'));
        static::assertSame('hello[nohelper]', $this->translator->trans('hello[nohelper]'));
        static::assertSame(
            'Tr',
            $this->translator->trans(
                'trainers: { count, number }[truncate:2|firstUpper]',
                [21629693]
            )
        );
    }

    public function testTranslateAddFilter(): void
    {
        $this->translator->addFilter(function ($message) {
            return \strrev($message);
        });

        static::assertSame('olleh', $this->translator->trans('hello'));
    }
}
