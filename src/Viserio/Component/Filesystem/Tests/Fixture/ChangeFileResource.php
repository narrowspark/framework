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

namespace Viserio\Component\Filesystem\Tests\Fixture;

use Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent;
use Viserio\Contract\Filesystem\Watcher\Resource as ResourceContract;

class ChangeFileResource implements ResourceContract
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function detectChanges(): array
    {
        return [new FileChangeEvent($this->path, FileChangeEvent::FILE_CHANGED)];
    }
}
