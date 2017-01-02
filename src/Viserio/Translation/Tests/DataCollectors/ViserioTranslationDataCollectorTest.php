<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\DataCollectors;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Translation\DataCollectors\ViserioTranslationDataCollector;
use Viserio\Translation\MessageCatalogue;
use Viserio\Translation\MessageSelector;
use Viserio\Translation\PluralizationRules;
use Viserio\Translation\Translator;

class ViserioTranslationDataCollectorTest extends TestCase
{
    use MockeryTrait;

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

        $selector = new MessageSelector();
        $selector->setPluralization(new PluralizationRules());

        $this->translator = new Translator(
            $catalogue,
            $selector
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetMenu()
    {
        $collector = new ViserioTranslationDataCollector($this->translator);

        static::assertEquals(
            [
            'icon'      => file_get_contents(__DIR__ . '/../../DataCollectors/Resources/icons/ic_translate_white_24px.svg'),
                'label' => '',
                'value' => null,
            ],
            $collector->getMenu()
        );
    }

    public function testGetTooltip()
    {
        $collector = new ViserioTranslationDataCollector($this->translator);

        static::assertSame('<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>Missing messages</b><span></span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Fallback messages</b><span></span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Defined messages</b><span></span></div></div>', $collector->getTooltip());
    }

    public function testGetPanel()
    {
        $collector = new ViserioTranslationDataCollector($this->translator);
        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertSame(
            $this->removeId('<div class="webprofiler-tabs row"><div class="webprofiler-tabs-tab col span_4"><input type="radio" name="tabgroup" id="tab-0-586ade7256e86"><label for="tab-0-586ade7256e86">Defined <span class="counter">0</span></label><div class="webprofiler-tabs-tab-content"><h3>These messages are correctly translated into the given locale.</h3><div class="empty">Empty</div></div></div><div class="webprofiler-tabs-tab col span_4"><input type="radio" name="tabgroup" id="tab-1-586ade7256e8d"><label for="tab-1-586ade7256e8d">Fallback <span class="counter">0</span></label><div class="webprofiler-tabs-tab-content"><h3>These messages are not available for the given locale but Symfony found them in the fallback locale catalog.</h3><div class="empty">Empty</div></div></div><div class="webprofiler-tabs-tab col span_4"><input type="radio" name="tabgroup" id="tab-2-586ade7256e93"><label for="tab-2-586ade7256e93">Missing <span class="counter">0</span></label><div class="webprofiler-tabs-tab-content"><h3>These messages are not available for the given locale and cannot be found in the fallback locales. <br> Add them to the translation catalogue to avoid Narrowspark outputting untranslated contents.</h3><div class="empty">Empty</div></div></div></div>'),
            $this->removeId($collector->getPanel())
        );
    }

    private function removeId(string $html): string
    {
        $html = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $html));

        return trim(preg_replace('/="tab-(.*?)"/', '', $html));
    }
}
