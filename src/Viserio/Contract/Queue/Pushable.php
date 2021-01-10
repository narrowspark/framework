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

namespace Viserio\Contract\Queue;

interface Pushable
{
    /**
     * Push a new message onto the queue.
     *
     * @param mixed    $data     The job's data
     * @param string   $info     Info text (used for logging)
     * @param array    $metadata Additional data about the job
     * @param null|int $delay    Delay in seconds (null for adapter default)
     */
    public function push($data, string $info, array $metadata = [], ?int $delay = null);
}
