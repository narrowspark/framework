<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

final class TraceableCollector
{
    /**
     * @var string $name
     */
    public $name;

    /**
     * @var int $start
     */
    public $start;

    /**
     * @var int $end
     */
    public $end;

    /**
     * @var int $result
     */
    public $result;

    /**
     * @var int $hits
     */
    public $hits = 0;

    /**
     * @var int $misses
     */
    public $misses = 0;
}
