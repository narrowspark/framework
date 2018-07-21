<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

interface CacheDecorator
{
    /**
     * Get the original class name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get a list of calls.
     *
     * @return array
     */
    public function getCalls(): array;
}
