<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\MessageSelector;
use Viserio\Component\Translation\PluralizationRules;
use Viserio\Component\Translation\Translator;

class TranslatorTest extends TestCase
{
    use MockeryTrait;

    private $translator;

    public function setUp()
    {
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

        $selector = new MessageSelector();
        $selector->setPluralization(new PluralizationRules());

        $this->translator = new Translator(
            $catalogue,
            $selector
        );
    }

    public function testGetSelectorAndCatalogue()
    {
        self::assertInstanceOf(MessageCatalogue::class, $this->translator->getCatalogue());
        self::assertInstanceOf(MessageSelector::class, $this->translator->getSelector());
    }

    public function testTrans()
    {
        self::assertSame('bar', $this->translator->trans('foo'));
    }

    public function testTransWithDomain()
    {
        self::assertSame('foo', $this->translator->trans('foo', [], 'admin'));
    }

    public function testTransWithVars()
    {
        self::assertSame('Hallo Daniel', $this->translator->trans('Hallo %name%', ['%name%' => 'Daniel']));
    }

    public function testTransChoice()
    {
        self::assertSame(
            'There is one apple',
            $this->translator->transChoice('{0} There are no apples|{1} There is one apple', 1)
        );

        self::assertSame(
            'There are no apples',
            $this->translator->transChoice('{0} There are no apples|{1} There is one apple', 0)
        );

        self::assertSame(
            'There is one apple',
            $this->translator->transChoice('{0} There are no apples|{1} There is one apple', ['one'])
        );
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
            $this->translator->transChoice('{0} There are no apples|{1} There is one apple', ['one'])
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
    }

    public function testTranslateAddHelperWithTransChoice()
    {
        $this->translator->addHelper('firstUpper', function ($translation) {
            return ucfirst($translation);
        });
        $this->translator->addHelper('truncate', function ($translation, $length) {
            return mb_substr($translation, 0, (int) $length);
        });

        self::assertSame(
            'Th',
            $this->translator->transChoice(
                '{0} There are no apples|{1} There is one apple[truncate:2|firstUpper]',
                ['one']
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
