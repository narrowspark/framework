<?php
declare(strict_types=1);
namespace Viserio\Contracts\WebProfiler;

interface TabAware
{
    /**
     * Returns infos for a tab.
     *  - label: use it for a icon
     *  - value: can be used to show a name or counte
     * @return array
     */
    public function getTab(): array;

    /**
     * Get the Tab postion from a collector.
     * Choose between left or right postion.
     *
     * @return string
     */
    public function getTabPosition(): string;
}
