<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\WebProfiler;

use Viserio\Component\Contracts\WebProfiler\DataCollector as DataCollectorContract;

interface WebProfiler
{
    /**
     * Adds a data collector.
     *
     * @param \Viserio\Component\Contracts\WebProfiler\DataCollector $collector
     * @param int                                                    $priority
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function addCollector(DataCollectorContract $collector, int $priority = 100): WebProfiler;

    /**
     * Checks if a data collector has been added.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCollector(string $name): bool;

    /**
     * Returns an array of all data collectors.
     *
     * @return array
     */
    public function getCollectors(): array;
}
