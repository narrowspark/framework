<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
