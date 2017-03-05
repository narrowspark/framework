<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\DataCollectors;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Translation\DataCollectors\ViserioTranslationDataCollector;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\MessageSelector;
use Viserio\Component\Translation\PluralizationRules;
use Viserio\Component\Translation\Translator;

class ViserioTranslationDataCollectorTest extends MockeryTestCase
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

        $selector = new MessageSelector();
        $selector->setPluralization(new PluralizationRules());

        $this->translator = new Translator(
            $catalogue,
            $selector
        );
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

    public function testGetPanelWithTranslation()
    {
        $this->translator->trans('foo');
        $this->translator->trans('foo');
        $this->translator->trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi pretium nulla non gravida interdum. In pretium eleifend lorem. Morbi porttitor, diam sed rutrum pretium, purus mauris lacinia arcu, nec eleifend metus ex eu nisl. Duis finibus ipsum at arcu iaculis, viverra sagittis erat viverra. In et nulla posuere, molestie turpis id, euismod erat. Suspendisse diam sem, cursus at eros non, venenatis tincidunt nisi. Morbi viverra, risus quis tempor lacinia, purus ligula pretium quam, non ornare turpis orci sit amet ex. Proin. purus ligula pretium quam, non ornare turpis orci sit amet ex.');
        $this->translator->trans('test');

        $collector = new ViserioTranslationDataCollector($this->translator);
        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertSame(
            '<div class="webprofiler-tabs row"><div class="webprofiler-tabs-tab col span_4"><input type="radio" name="tabgroup" id><label for>Defined <span class="counter">1</span></label><div class="webprofiler-tabs-tab-content"><h3>These messages are correctly translated into the given locale.</h3><table class="row"><thead><tr><th scope="col" class="Locale">Locale</th><th scope="col" class="Domain">Domain</th><th scope="col" class="Times used">Times used</th><th scope="col" class="Message ID">Message ID</th><th scope="col" class="Message Preview">Message Preview</th></tr></thead><tbody><tr><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="2 characters">en</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="8 characters">messages</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad=" "><span class=sf-dump-num>2</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="3 characters">foo</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="3 characters">bar</span>"
</pre>
</td></tr></tbody></table></div></div><div class="webprofiler-tabs-tab col span_4"><input type="radio" name="tabgroup" id><label for>Fallback <span class="counter">1</span></label><div class="webprofiler-tabs-tab-content"><h3>These messages are not available for the given locale but Symfony found them in the fallback locale catalog.</h3><table class="row"><thead><tr><th scope="col" class="Locale">Locale</th><th scope="col" class="Domain">Domain</th><th scope="col" class="Times used">Times used</th><th scope="col" class="Message ID">Message ID</th><th scope="col" class="Message Preview">Message Preview</th></tr></thead><tbody><tr><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="2 characters">fr</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="8 characters">messages</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad=" "><span class=sf-dump-num>1</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="4 characters">test</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="3 characters">bar</span>"
</pre>
</td></tr></tbody></table></div></div><div class="webprofiler-tabs-tab col span_4"><input type="radio" name="tabgroup" id><label for>Missing <span class="counter">1</span></label><div class="webprofiler-tabs-tab-content"><h3>These messages are not available for the given locale and cannot be found in the fallback locales. <br> Add them to the translation catalogue to avoid Narrowspark outputting untranslated contents.</h3><table class="row"><thead><tr><th scope="col" class="Locale">Locale</th><th scope="col" class="Domain">Domain</th><th scope="col" class="Times used">Times used</th><th scope="col" class="Message ID">Message ID</th><th scope="col" class="Message Preview">Message Preview</th></tr></thead><tbody><tr><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="2 characters">en</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="8 characters">messages</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad=" "><span class=sf-dump-num>1</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="590 characters">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi pretium nulla non gravida interdum. In pretium eleifend lorem. Morbi porttitor, diam sed rutrum pretium, purus mauris lacinia arcu, nec eleifend metus ex eu nisl. Duis finibus ipsum at arcu iaculis, viverra sagittis erat viverra. In et nulla posuere, molestie turpis id, euismod erat. Suspendisse diam sem, cursus at eros non, venenatis tincidunt nisi. Morbi viverra, risus quis tempor lacinia, purus ligula pretium quam, non ornare turpis orci sit amet ex. Proin. purus ligula pretium quam, non ornare turpis orci sit amet ex.</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad=" ">"<span class=sf-dump-str title="590 characters">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi pretium nulla non gravida interdum. In pretium eleifend lorem. Morbi porttitor, diam sed rutrum pretium, purus mauris lacinia arcu, nec eleifend metus ex eu nisl. Duis finibus ipsum at arcu iaculis, viverra sagittis erat viverra. In et nulla posuere, molestie turpis id, euismod erat. Suspendisse diam sem, cursus at eros non, venenatis tincidunt nisi. Morbi viverra, risus quis tempor lacinia, purus ligula pretium quam, non ornare turpis orci sit amet ex. Proin. purus ligula pretium quam, non ornare turpis orci sit amet ex.</span>"
</pre>
</td></tr></tbody></table></div></div></div>',
            $this->removeId($collector->getPanel())
        );
    }

    private function removeId(string $html): string
    {
        $html = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $html));
        $html = preg_replace('/id=sf-dump-(?:\d+) /', '', $html);
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/', '', $html);

        return trim(preg_replace('/="tab-(.*?)"/', '', $html));
    }
}
