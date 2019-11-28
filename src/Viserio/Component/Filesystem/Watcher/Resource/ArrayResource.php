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
