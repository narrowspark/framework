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

namespace Viserio\Component\Filesystem\Watcher\Resource;

use Viserio\Contract\Filesystem\Watcher\Resource as ResourceContract;

/**
 * @internal
 */
final class ArrayResource implements ResourceContract
{
    /** @var \Viserio\Contract\Filesystem\Watcher\Resource[] */
    private $resources;

    /**
     * Create a new ArrayResource instance.
     *
     * @param \Viserio\Contract\Filesystem\Watcher\Resource[] $resources
     */
    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent[]
     */
    public function detectChanges(): array
    {
        $events = [];

        foreach ($this->resources as $resource) {
            if (\count($changes = $resource->detectChanges()) !== 0) {
                foreach ($changes as $change) {
                    $events[] = $change;
                }
            }
        }

        return $events;
    }
}
