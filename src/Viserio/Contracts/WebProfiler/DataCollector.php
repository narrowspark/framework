<?php
declare(strict_types=1);
namespace Viserio\Contracts\WebProfiler;

interface DataCollector
{
    /**
     * Called by the webprofiler when data needs to be collected
     *
     * @return array Collected data
     */
    function collect(): array;

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    function getName(): string;
}
