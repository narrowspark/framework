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

namespace Viserio\Component\Filesystem\Watcher\Event;

class FileChangeEvent
{
    public const FILE_CHANGED = 1;
    public const FILE_DELETED = 2;
    public const FILE_CREATED = 3;

    private $file;

    private $event;

    public function __construct(string $file, int $event)
    {
        $this->file = $file;
        $this->event = $event;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getEvent(): int
    {
        return $this->event;
    }
}
