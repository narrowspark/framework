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

namespace Viserio\Component\Filesystem\Watcher;

use Generator;
use Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent as FileChangeEvent;
use Viserio\Contract\Filesystem\Exception\IOException;
use Viserio\Contract\Filesystem\Exception\RuntimeException;
use Viserio\Contract\Filesystem\Watcher\Watcher as WatcherContract;
use function Viserio\Component\Filesystem\glob;

/**
 * Inotify tracker. To use this tracker you must install inotify extension.
 *
 * @see http://pecl.php.net/package/inotify Inotify PECL extension
 */
final class INotifyWatcher implements WatcherContract
{
    /**
     * {@inheritdoc}
     */
    public function watch($path, callable $callback, ?int $timeout = null): void
    {
        $inotifyInit = \inotify_init();

        if ($inotifyInit === false) {
            throw new IOException('Unable initialize inotify.', 0, null, $path);
        }

        \stream_set_blocking($inotifyInit, false);

        $isDir = \is_dir($path);
        $watchers = [];

        if ($isDir) {
            $watchers[] = \inotify_add_watch($inotifyInit, $path, \IN_CREATE | \IN_DELETE | \IN_MODIFY);

            foreach ($this->scanPath("{$path}/*") as $p) {
                $watchers[] = \inotify_add_watch($inotifyInit, $p, \IN_CREATE | \IN_DELETE | \IN_MODIFY);
            }
        } else {
            $watchers[] = \inotify_add_watch($inotifyInit, $path, \IN_MODIFY);
        }

        try {
            $read = [$inotifyInit];
            $write = null;
            $except = null;
            $tvSec = $timeout === null ? null : 0;
            $tvUsec = $timeout === null ? null : $timeout * 1000;

            while (true) {
                if (\stream_select($read, $write, $except, $tvSec, $tvUsec) === 0) {
                    $read = [$inotifyInit];

                    continue;
                }

                $events = \inotify_read($inotifyInit);

                if ($events === false) {
                    continue;
                }

                $last = \end($events);

                if ($last['mask'] === \IN_Q_OVERFLOW) {
                    throw new RuntimeException('Event queue overflowed. Either read events more frequently or increase the limit for queues. The limit can be changed in /proc/sys/fs/inotify/max_queued_events.');
                }

                foreach ($events as $event) {
                    $code = null;

                    switch ($event['mask']) {
                        case \IN_CREATE:
                            $code = FileChangeEvent::FILE_CREATED;

                            break;
                        case \IN_DELETE:
                            $code = FileChangeEvent::FILE_DELETED;

                            break;
                        case \IN_MODIFY:
                            $code = FileChangeEvent::FILE_CHANGED;

                            break;
                    }

                    if ($callback(($isDir ? $path : '') . $event['name'], $code) === false) {
                        break;
                    }
                }
            }
        } finally {
            foreach ($watchers as $watchId) {
                \inotify_rm_watch($inotifyInit, $watchId);
            }

            \fclose($inotifyInit);
        }
    }

    /**
     * @param string $path
     *
     * @return Generator
     */
    private function scanPath(string $path): Generator
    {
        foreach (glob($path, \GLOB_ONLYDIR) as $directory) {
            yield $directory;
            yield from $this->scanPath("{$directory}/*");
        }
    }
}
