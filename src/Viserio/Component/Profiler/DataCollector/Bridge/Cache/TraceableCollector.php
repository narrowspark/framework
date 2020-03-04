<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

/** @internal */
final class TraceableCollector
{
    /** @var string */
    public $name;

    /** @var float */
    public $start;

    /** @var float */
    public $end;

    /** @var array|bool */
    public $result;

    /** @var int */
    public $hits = 0;

    /** @var int */
    public $misses = 0;
}
