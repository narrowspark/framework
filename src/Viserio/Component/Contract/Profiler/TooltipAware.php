<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Profiler;

interface TooltipAware extends DataCollector
{
    /**
     * Returns infos for a tooltip.
     *
     * @return string
     */
    public function getTooltip(): string;
}
