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
    public function push($data, string $info, array $metadata = [], int $delay = null);
}
