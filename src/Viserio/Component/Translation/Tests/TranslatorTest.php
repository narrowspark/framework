<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\Translator;

/**
 * @internal
 */
final class TranslatorTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Translation\Translator
     */
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
        $this->assertSame('bar', $this->translator->trans('foo'));

        $this->assertSame(
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

        $this->assertSame(
            'She avoids bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'female'])
        );

        $this->assertSame(
            'They avoid bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'other'])
        );
    }

    public function testTransWithDomain(): void
    {
        $this->assertSame('foo', $this->translator->trans('foo', [], 'admin'));
    }

    public function testTransWithVars(): void
    {
        $this->assertSame('Hallo Daniel', $this->translator->trans('Hallo {name}', ['name' => 'Daniel']));
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

        $this->assertSame('bar', $this->translator->trans('test'));
        $this->assertSame('dont', $this->translator->trans('dont'));

        $this->assertSame(
            'They avoid bugs',
            $this->translator->trans('{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }', ['gender' => 'other'])
        );

        $this->assertSame('bar', $this->translator->trans('foo'));

        $this->translator->getCatalogue()->getFallbackCatalogue()->addFallbackCatalogue(new MessageCatalogue('de', [
            'messages' => [
                'wurst' => 'salat',
            ],
        ]));

        $this->assertSame('salat', $this->translator->trans('wurst'));
    }

    public function testTranslateAddHelper(): void
    {
        $this->translator->addHelper('firstUpper', static function ($translation) {
            return \ucfirst($translation);
        });
        $this->translator->addHelper('truncate', static function ($translation, $length) {
            return \mb_substr($translation, 0, (int) $length);
        });

        $this->assertSame('He', $this->translator->trans('hello[truncate:2|firstUpper]'));
        $this->assertSame('hello[nohelper]', $this->translator->trans('hello[nohelper]'));
        $this->assertSame(
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

        $this->assertSame('olleh', $this->translator->trans('hello'));
    }
}
