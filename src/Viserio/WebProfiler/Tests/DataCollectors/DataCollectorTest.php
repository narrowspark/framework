<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors;

use Viserio\WebProfiler\Tests\Fixture\FixtureDataCollector;

class DataCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $collector = new FixtureDataCollector();

        $this->assertSame('fixture-data-collector', $collector->getName());
    }

    public function testGetMenuPosition($value='')
    {
        $this->assertSame('left', (new FixtureDataCollector())->getMenuPosition());
    }

    public function testCreateTable()
    {
        $collector    = new FixtureDataCollector();
        $defaultTable = file_get_contents(__DIR__ . '/../Fixture/View/default_table.html');

        $this->assertSame(
            $this->removeSymfonyVarDumper($defaultTable),
            $this->removeSymfonyVarDumper($collector->getTableDefault())
        );
    }

    public function testCreateTooltipGroupDefault()
    {
        $collector = new FixtureDataCollector();

        $this->assertSame(
            '<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>test</b><span>test</span></div></div>',
            $collector->getTooltippGroupDefault()
        );
    }

    public function testCreateTooltipGroupArray()
    {
        $collector = new FixtureDataCollector();

        $this->assertSame(
            '<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>test</b><span class="test">test</span><span class="test2">test2</span></div></div>',
            $collector->getTooltippGroupArray()
        );
    }

    public function testCreateTabs()
    {
        $collector = new FixtureDataCollector();

        $this->assertSame(
            $this->removeTabId('<div class="webprofiler-tabs row"><div class="webprofiler-tabs-tab col span_12"><input type="radio" name="tabgroup" id="tab-0-5857be8b2c3d4"><label for="tab-0-5857be8b2c3d4">test</label><div class="webprofiler-tabs-tab-content">test</div></div></div>'),
            $this->removeTabId($collector->getTabs())
        );
    }

    public function testCreateDropdownMenuContent()
    {
        $collector = new FixtureDataCollector();

        $this->assertSame(
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
