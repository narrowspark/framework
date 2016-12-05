<?php
declare(strict_types=1);
namespace Viserio\Contracts\WebProfiler;

interface LateDataCollector
{
    /**
     * Collects data as late as possible.
     */
    public function lateCollect();
}
