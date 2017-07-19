<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Translation\DataCollector\ViserioTranslationDataCollector;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\Translator;

class ViserioTranslationDataCollectorTest extends MockeryTestCase
{
    private $translator;

    public function setUp(): void
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

        self::assertEquals(
            [
            'icon'      => \file_get_contents(__DIR__ . '/../../DataCollector/Resources/icons/ic_translate_white_24px.svg'),
                'label' => '',
                'value' => null,
            ],
            $collector->getMenu()
        );
    }

    public function testGetTooltip(): void
    {
        $collector = new ViserioTranslationDataCollector($this->translator);

        self::assertSame('<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Missing messages</b><span></span></div><div class="profiler-menu-tooltip-group-piece"><b>Fallback messages</b><span></span></div><div class="profiler-menu-tooltip-group-piece"><b>Defined messages</b><span></span></div></div>', $collector->getTooltip());
    }

    private function removeId(string $html): string
    {
        $html = \preg_replace('/[ \t]+/', ' ', \preg_replace('/[\r\n]+/', "\n", $html));
        $html = \preg_replace('/id=sf-dump-(?:\d+) /', '', $html);
        $html = \preg_replace('/<script\b[^>]*>(.*?)<\/script>/', '', $html);
        $html = \preg_replace('/<style\b[^>]*>(.*?)<\/style>/', '', $html);

        return \trim(\preg_replace('/="tab-(.*?)"/', '', $html));
    }
}
