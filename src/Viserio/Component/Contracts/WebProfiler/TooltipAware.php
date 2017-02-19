<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\WebProfiler;

interface TooltipAware
{
    /**
     * Returns infos for a tooltip.
     *
     * @return string
     */
    public function getTooltip(): string;
}
