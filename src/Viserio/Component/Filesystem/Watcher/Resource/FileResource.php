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

use Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent;
use Viserio\Contract\Filesystem\Watcher\Resource as ResourceContract;

final class FileResource implements ResourceContract
{
    /** @var string */
    private $file;

    /** @var false|int */
    private $lastModified;

    /**
     * Create a new FileResource instance.
     *
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;
        $this->lastModified = \filemtime($file);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent[]
     */
    public function detectChanges(): array
    {
        if ($this->isModified()) {
            $this->lastModified = \filemtime($this->file); // update modified time

            return [new FileChangeEvent($this->file, FileChangeEvent::FILE_CHANGED)];
        }

        return [];
    }

    /**
     * Check if the file is modified.
     *
     * @return bool
     */
    private function isModified(): bool
    {
        \clearstatcache(false, $this->file);

        return $this->lastModified < \filemtime($this->file);
    }
}
