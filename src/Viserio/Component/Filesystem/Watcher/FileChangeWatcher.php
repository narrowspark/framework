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
use Viserio\Contract\Filesystem\Watcher\Adapter as AdapterContract;

final class FileChangeWatcher implements AdapterContract
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
    public function isSupported(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function watch($path, callable $callback, ?int $timeout = null): void
    {
        if ($timeout === null) {
            $timeout = 1000;
        }

        /** @var \Viserio\Component\Filesystem\Watcher\Resource\Locator\FileResourceLocator $locator */
        $locator = $this->locator;
        $resource = $locator::locate($path);

        if ($resource === null) {
            throw new InvalidArgumentException(\sprintf('[%s] is not a valid path to watch.', \gettype($path)));
        }

        $run = true;

        while ($run) {
            /** @var \Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent[] $changes */
            if (\count($changes = $resource->detectChanges()) !== 0) {
                /** @var \Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent $change */
                foreach ($changes as $change) {
                    $run = $callback($change->getFile(), $change->getEvent()) !== false;
                }
            }

            \usleep($timeout * 1000);
        }
    }
}
