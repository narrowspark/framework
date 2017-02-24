<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\WebProfiler;

interface MenuAware extends DataCollector
{
    /**
     * Returns infos for a tab.
     *  - icon
     *  - label
     *  - value.
     *
     * @return array
     */
    public function getMenu(): array;

    /**
     * Get the Tab postion from a collector.
     * Choose between left or right postion.
     *
     * @return string
     */
    public function getMenuPosition(): string;
}
