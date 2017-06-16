<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Contracts\Translation\MessageFormatter as MessageFormatterContract;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\Translator;

class TranslatorTest extends MockeryTestCase
{
    private $translator;

    public function setUp()
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

    public function testGetFormatterAndCatalogue()
    {
        self::assertInstanceOf(MessageCatalogue::class, $this->translator->getCatalogue());
        self::assertInstanceOf(MessageFormatterContract::class, $this->translator->getFormatter());
    }

    public function testTrans()
    {
        self::assertSame('bar', $this->translator->trans('foo'));

        self::assertSame(
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

        self::assertSame(
            'She avoids bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'female'])
        );

        self::assertSame(
            'They avoid bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'other'])
        );
    }

    public function testTransWithDomain()
    {
        self::assertSame('foo', $this->translator->trans('foo', [], 'admin'));
    }

    public function testTransWithVars()
    {
        self::assertSame('Hallo Daniel', $this->translator->trans('Hallo {name}', ['name' => 'Daniel']));
    }

    public function testSetAndGetLogger()
    {
        $logger = $this->mock(LoggerInterface::class);
        $logger
            ->shouldReceive('debug')
            ->twice();
        $logger
            ->shouldReceive('warning')
            ->twice();
        $this->translator->setLogger($logger);

        self::assertInstanceOf(LoggerInterface::class, $this->translator->getLogger());

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

    public function testTranslateAddHelper()
    {
        $this->translator->addHelper('firstUpper', function ($translation) {
            return ucfirst($translation);
        });
        $this->translator->addHelper('truncate', function ($translation, $length) {
            return mb_substr($translation, 0, (int) $length);
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

    public function testTranslateAddFilter()
    {
        $this->translator->addFilter(function ($message) {
            return strrev($message);
        });

        self::assertSame('olleh', $this->translator->trans('hello'));
    }
}
