<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\WebProfiler;

interface PanelAware extends DataCollector
{
    /**
     * Returns all data in a panel window.
     *
     * @return string
     */
    public function getPanel(): string;
}
