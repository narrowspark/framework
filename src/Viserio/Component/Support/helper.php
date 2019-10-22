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

if (! \function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param int      $times
     * @param callable $callback
     * @param int      $sleep
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    function retry(int $times, callable $callback, int $sleep = 0)
    {
        $times--;
        beginning:

        try {
            return $callback();
        } catch (Throwable $e) {
            if ($times === 0) {
                throw $e;
            }

            $times--;

            if ($sleep !== 0) {
                \usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}
