<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

/** @internal */
final class TraceableCollector
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $start;

    /**
     * @var int
     */
    public $end;

    /**
     * @var array|bool
     */
    public $result;

    /**
     * @var int
     */
    public $hits = 0;

    /**
     * @var int
     */
    public $misses = 0;
}
