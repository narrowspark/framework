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
