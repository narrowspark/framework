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

namespace Viserio\Component\Filesystem\Watcher;

use Generator;
use Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent as FileChangeEvent;
use Viserio\Contract\Filesystem\Exception\IOException;
use Viserio\Contract\Filesystem\Exception\RuntimeException;
use Viserio\Contract\Filesystem\Watcher\Adapter as AdapterContract;
use function Viserio\Component\Finder\glob;

/**
 * Inotify tracker. To use this tracker you must install inotify extension.
 *
 * @see http://pecl.php.net/package/inotify Inotify PECL extension
 */
final class INotifyWatcher implements AdapterContract
{
    /**
     * {@inheritdoc}
     */
    public function isSupported(): bool
    {
        return \extension_loaded('inotify');
    }

    /**
     * {@inheritdoc}
     */
    public function watch($path, callable $callback, ?int $timeout = null): void
    {
        /** @var bool|resource $inotifyInit */
        $inotifyInit = \inotify_init();

        if (! \is_resource($inotifyInit)) {
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

                /** @var array<int, array<string, int>>|bool $events */
                $events = \inotify_read($inotifyInit);

                if (! \is_array($events)) {
                    continue;
                }

                /** @var array<string, int> $last */
                $last = \end($events);

                if ($last['mask'] === \IN_Q_OVERFLOW) {
                    throw new RuntimeException('Event queue overflowed. Either read events more frequently or increase the limit for queues. The limit can be changed in /proc/sys/fs/inotify/max_queued_events.');
                }

                /** @var array<string, int> $event */
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
     * @return Generator<string>
     */
    private function scanPath(string $path): Generator
    {
        foreach (glob($path, \GLOB_ONLYDIR) as $directory) {
            yield $directory;

            yield from $this->scanPath("{$directory}/*");
        }
    }
}
