<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Translation\DataCollector\ViserioTranslationDataCollector;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\Translator;

/**
 * @internal
 */
final class ViserioTranslationDataCollectorTest extends MockeryTestCase
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

    public function testGetMenu(): void
    {
        $collector = new ViserioTranslationDataCollector($this->translator);

        static::assertEquals(
            [
                'icon'      => \file_get_contents(__DIR__ . '/../../Resource/icons/ic_translate_white_24px.svg'),
                'label'     => '',
                'value'     => null,
            ],
            $collector->getMenu()
        );
    }

    public function testGetTooltip(): void
    {
        $collector = new ViserioTranslationDataCollector($this->translator);

        static::assertSame('<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Missing messages</b><span></span></div><div class="profiler-menu-tooltip-group-piece"><b>Fallback messages</b><span></span></div><div class="profiler-menu-tooltip-group-piece"><b>Defined messages</b><span></span></div></div>', $collector->getTooltip());
    }
}
