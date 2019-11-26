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

use InvalidArgumentException;
use Viserio\Component\Filesystem\Watcher\Resource\Locator\FileResourceLocator;
use Viserio\Contract\Filesystem\Watcher\Watcher as WatcherContract;

final class FileChangeWatcher implements WatcherContract
{
    /**
     * A Locator implementation.
     *
     * @var string
     */
    private $locator = FileResourceLocator::class;

    /**
     * {@inheritdoc}
     */
    public function watch($path, callable $callback, ?float $timeout = null): void
    {
        if (null === $timeout) {
            $timeout = 1000;
        }

        /** @var \Viserio\Component\Filesystem\Watcher\Resource\Locator\FileResourceLocator $locator */
        $locator = $this->locator;
        $resource = $locator::locate($path);

        if (! $resource) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid path to watch', \gettype($path)));
        }
        $run = true;

        while ($run) {
            /** @var \Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent[] $changes */
            if ($changes = $resource->detectChanges()) {
                foreach ($changes as $change) {
                    $run = false !== $callback($change->getFile(), $change->getEvent());
                }
            }

            \usleep($timeout * 1000);
        }
    }
}
