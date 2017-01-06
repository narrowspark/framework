<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors;

use PHPUnit\Framework\TestCase;
use Viserio\WebProfiler\Tests\Fixture\FixtureDataCollector;

class DataCollectorTest extends TestCase
{
    public function testGetName()
    {
        $collector = new FixtureDataCollector();

        static::assertSame('fixture-data-collector', $collector->getName());
    }

    public function testGetMenuPosition()
    {
        static::assertSame('left', (new FixtureDataCollector())->getMenuPosition());
    }

    public function testCreateTableDefault()
    {
        $collector    = new FixtureDataCollector();
        $defaultTable = file_get_contents(__DIR__ . '/../Fixture/View/default_table.html');

        static::assertSame(
            $this->removeSymfonyVarDumper($defaultTable),
            $this->removeSymfonyVarDumper($collector->getTableDefault())
        );
    }

    public function testCreateTableArray()
    {
        $collector    = new FixtureDataCollector();
        $defaultTable = file_get_contents(__DIR__ . '/../Fixture/View/array_table.html');

        static::assertSame(
            $this->removeSymfonyVarDumper($defaultTable),
            $this->removeSymfonyVarDumper($collector->getTableArray())
        );
    }

    public function testCreateTooltipGroupDefault()
    {
        $collector = new FixtureDataCollector();

        static::assertSame(
            '<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>test</b><span>test</span></div></div>',
            $collector->getTooltippGroupDefault()
        );
    }

    public function testCreateTooltipGroupDefaultWithLink()
    {
        $collector = new FixtureDataCollector();

        static::assertSame(
            '<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>Resources</b><span><a href="//narrowspark.de/doc/">Read Narrowspark Doc\'s </a></span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Help</b><span><a href="//narrowspark.de/support">Narrowspark Support Channels</a></span></div></div>',
            $collector->getTooltippGroupDefaultWithLink()
        );
    }

    public function testCreateTooltipGroupArray()
    {
        $collector = new FixtureDataCollector();

        static::assertSame(
            '<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>test</b><span class="test">test</span><span class="test2">test2</span></div></div>',
            $collector->getTooltippGroupArray()
        );
    }

    public function testCreateTabs()
    {
        $collector = new FixtureDataCollector();

        static::assertSame(
            $this->removeTabId('<div class="webprofiler-tabs row"><div class="webprofiler-tabs-tab col span_12"><input type="radio" name="tabgroup" id="tab-0-5857be8b2c3d4"><label for="tab-0-5857be8b2c3d4">test</label><div class="webprofiler-tabs-tab-content">test</div></div></div>'),
            $this->removeTabId($collector->getTabs())
        );
    }

    public function testCreateDropdownMenuContent()
    {
        $collector = new FixtureDataCollector();

        static::assertSame(
            $this->removeDropdownMenuId('<select class="content-selector" name="fixture-data-collector"><option value="content-dropdown-5858e9e677a84"selected>dropdown</option></select><div id="content-dropdown-5858e9e677a84" class="selected-content">content</div>'),
            $this->removeDropdownMenuId($collector->getDropdownMenuContent())
        );
    }

    private function removeSymfonyVarDumper(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/', '', $html);

        return trim(preg_replace('/id=sf-dump-(?:\d+) /', '', $html));
    }

    private function removeTabId(string $html): string
    {
        return trim(preg_replace('/="tab-0(.*?)"/', '', $html));
    }

    private function removeDropdownMenuId(string $html): string
    {
        return trim(preg_replace('/="content-dropdown(.*?)"/', '', $html));
    }
}
