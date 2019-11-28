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
     * @return void
     */
    public function watch($path, callable $callback, ?int $timeout = null): void;
}
