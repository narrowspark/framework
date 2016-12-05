<?php
declare(strict_types=1);
namespace Viserio\Contracts\WebProfiler;

interface DataCollector
{
    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    function getName(): string;
}
