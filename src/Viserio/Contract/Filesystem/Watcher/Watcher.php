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

namespace Viserio\Contract\Filesystem\Watcher;

interface Watcher
{
    /**
     * Watches a file or directory for any changes, and calls $callback when any changes are detected.
     *
     * @param mixed    $path     The path to watch for changes. Can be a path to a file or directory, iterator or array with paths
     * @param callable $callback The callback to execute when a change is detected
     * @param null|int $timeout  The time in milliseconds to wait between checking for changes (defaults to 1000 when inotify is not available)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\RuntimeException
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     */
    public function watch($path, callable $callback, ?int $timeout = null): void;
}
