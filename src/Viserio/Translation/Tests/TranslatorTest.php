<?php

declare(strict_types=1);
namespace Viserio\Translation\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Log\LoggerInterface;
use Viserio\Translation\{
    MessageCatalogue,
    MessageSelector,
    PluralizationRules,
    Translator
};

class TranslatorTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf(MessageCatalogue::class, $this->translator->getCatalogue());
        $this->assertInstanceOf(MessageSelector::class, $this->translator->getSelector());
    }

    public function testTrans()
    {
        $this->assertSame('bar', $this->translator->trans('foo'));
    }

    public function testTransWithDomain()
    {
        $this->assertSame('foo', $this->translator->trans('foo', [], 'admin'));
    }

    public function testTransWithVars()
    {
        $this->assertSame('Hallo Daniel', $this->translator->trans('Hallo %name%', ['%name%' => 'Daniel']));
    }

    public function testTransChoice()
    {
        $this->assertSame(
            'There is one apple',
            $this->translator->transChoice('{0} There are no apples|{1} There is one apple', 1)
        );

        $this->assertSame(
            'There are no apples',
            $this->translator->transChoice('{0} There are no apples|{1} There is one apple', 0)
        );

        $this->assertSame(
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

        $this->assertInstanceOf(LoggerInterface::class, $this->translator->getLogger());

        $this->assertSame('bar', $this->translator->trans('test'));
        $this->assertSame('dont', $this->translator->trans('dont'));

        $this->assertSame(
            'There is one apple',
            $this->translator->transChoice('{0} There are no apples|{1} There is one apple', ['one'])
        );

        $this->assertSame('bar', $this->translator->trans('foo'));
    }

    public function testTranslateAddHelper()
    {
        $this->translator->addHelper('firstUpper', function ($translation) {
            return ucfirst($translation);
        });
        $this->translator->addHelper('truncate', function ($translation, $length) {
            return substr($translation, 0, $length);
        });

        $this->assertSame('He', $this->translator->trans('hello[truncate:2|firstUpper]'));
        $this->assertSame('hello[nohelper]', $this->translator->trans('hello[nohelper]'));
    }

    public function testTranslateAddHelperWithTransChoice()
    {
        $this->translator->addHelper('firstUpper', function ($translation) {
            return ucfirst($translation);
        });
        $this->translator->addHelper('truncate', function ($translation, $length) {
            return substr($translation, 0, $length);
        });

        $this->assertSame(
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

        $this->assertSame('olleh', $this->translator->trans('hello'));
    }
}
