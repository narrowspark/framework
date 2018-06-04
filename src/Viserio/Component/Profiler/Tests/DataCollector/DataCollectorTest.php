<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\Tests\Fixture\FixtureDataCollector;

/**
 * @internal
 */
final class DataCollectorTest extends TestCase
{
    public function testGetName(): void
    {
        $collector = new FixtureDataCollector();

        $this->assertSame('fixture-data-collector', $collector->getName());
    }

    public function testGetMenuPosition(): void
    {
        $this->assertSame('left', (new FixtureDataCollector())->getMenuPosition());
    }

    public function testCreateTableDefault(): void
    {
        $collector    = new FixtureDataCollector();
        $defaultTable = \file_get_contents(__DIR__ . '/../Fixture/View/default_table.html');

        $this->assertSame(
            $this->removeSymfonyVarDumper(\preg_replace('/(\r\n|\n\r|\r)/', "\n", $defaultTable)),
            $this->removeSymfonyVarDumper(\preg_replace('/(\r\n|\n\r|\r)/', "\n", $collector->getTableDefault()))
        );
    }

    public function testCreateTableArray(): void
    {
        $collector    = new FixtureDataCollector();
        $defaultTable = \file_get_contents(__DIR__ . '/../Fixture/View/array_table.html');

        $this->assertSame(
            $this->removeSymfonyVarDumper(\preg_replace('/(\r\n|\n\r|\r)/', "\n", $defaultTable)),
            $this->removeSymfonyVarDumper(\preg_replace('/(\r\n|\n\r|\r)/', "\n", $collector->getTableArray()))
        );
    }

    public function testCreateTooltipGroupDefault(): void
    {
        $collector = new FixtureDataCollector();

        $this->assertSame(
            '<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>test</b><span>test</span></div></div>',
            $collector->getTooltippGroupDefault()
        );
    }

    public function testCreateTooltipGroupDefaultWithLink(): void
    {
        $collector = new FixtureDataCollector();

        $this->assertSame(
            '<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Resource</b><span><a href="//narrowspark.de/doc/">Read Narrowspark Doc\'s </a></span></div><div class="profiler-menu-tooltip-group-piece"><b>Help</b><span><a href="//narrowspark.de/support">Narrowspark Support Channels</a></span></div></div>',
            $collector->getTooltippGroupDefaultWithLink()
        );
    }

    public function testCreateTooltipGroupArray(): void
    {
        $collector = new FixtureDataCollector();

        $this->assertSame(
            '<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>test</b><span class="test">test</span><span class="test2">test2</span></div></div>',
            $collector->getTooltippGroupArray()
        );
    }

    public function testCreateTabs(): void
    {
        $collector = new FixtureDataCollector();

        $this->assertSame(
            $this->removeTabId('<div class="profiler-tabs row"><div class="profiler-tabs-tab col"><input type="radio" name="tabgroup" id="tab-0-5857be8b2c3d4"><label for="tab-0-5857be8b2c3d4">test</label><div class="profiler-tabs-tab-content">test</div></div></div>'),
            $this->removeTabId($collector->getTabs())
        );
    }

    public function testCreateDropdownMenuContent(): void
    {
        $collector = new FixtureDataCollector();

        $this->assertSame(
            $this->removeDropdownMenuId('<select class="content-selector" name="fixture-data-collector"><option value="content-dropdown-5858e9e677a84"selected>dropdown</option></select><div id="content-dropdown-5858e9e677a84" class="selected-content">content</div>'),
            $this->removeDropdownMenuId($collector->getDropdownMenuContent())
        );
    }

    private function removeSymfonyVarDumper(string $html): string
    {
        $html = \preg_replace('/<script\b[^>]*>(.*?)<\/script>/', '', $html);
        $html = \preg_replace('/<style\b[^>]*>(.*?)<\/style>/', '', $html);

        return \trim(\preg_replace('/id=sf-dump-(?:\d+) /', '', $html));
    }

    private function removeTabId(string $html): string
    {
        return \trim(\preg_replace('/="tab-0(.*?)"/', '', $html));
    }

    private function removeDropdownMenuId(string $html): string
    {
        return \trim(\preg_replace('/="content-dropdown(.*?)"/', '', $html));
    }
}
