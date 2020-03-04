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
     */
    private function isModified(): bool
    {
        \clearstatcache(false, $this->file);

        return $this->lastModified < \filemtime($this->file);
    }
}
