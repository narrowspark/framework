<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Contracts\Translation\MessageFormatter as MessageFormatterContract;
use Viserio\Component\Translation\Formatters\MessageFormatter;
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
            new MessageFormatter()
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
            'There is one apple',
            $this->translator->trans('{0} There are no apples|{1} There is one apple', [1])
        );

        self::assertSame(
            'There are no apples',
            $this->translator->trans('{0} There are no apples|{1} There is one apple', [0])
        );

        self::assertSame(
            'There is one apple',
            $this->translator->trans('{0} There are no apples|{1} There is one apple', [1])
        );
    }

    public function testTransWithDomain()
    {
        self::assertSame('foo', $this->translator->trans('foo', [], 'admin'));
    }

    public function testTransWithVars()
    {
        self::assertSame('Hallo Daniel', $this->translator->trans('Hallo %name%', ['%name%' => 'Daniel']));
    }

    public function testSetAndGetLogger()
    {
        $logger = $this->mock(LoggerInterface::class);
        $logger
            ->shouldReceive('debug')
            ->once();
        $logger
            ->shouldReceive('warning')
            ->twice();
        $this->translator->setLogger($logger);

        self::assertInstanceOf(LoggerInterface::class, $this->translator->getLogger());

        self::assertSame('bar', $this->translator->trans('test'));
        self::assertSame('dont', $this->translator->trans('dont'));

        self::assertSame(
            'There is one apple',
            $this->translator->trans('{0} There are no apples|{1} There is one apple', [1])
        );

        self::assertSame('bar', $this->translator->trans('foo'));
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
            'Th',
            $this->translator->trans(
                '{0} There are no apples|{1} There is one apple[truncate:2|firstUpper]',
                [1]
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
