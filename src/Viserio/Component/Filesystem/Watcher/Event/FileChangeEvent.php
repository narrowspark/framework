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

namespace Viserio\Component\Filesystem\Watcher\Event;

final class FileChangeEvent
{
    /** @var int */
    public const FILE_CHANGED = 1;

    /** @var int */
    public const FILE_DELETED = 2;

    /** @var int */
    public const FILE_CREATED = 3;

    /** @var string */
    private $file;

    /** @var int */
    private $event;

    /**
     * Create a new FileChangeEvent instance.
     */
    public function __construct(string $file, int $event)
    {
        $this->file = $file;
        $this->event = $event;
    }

    /**
     * Returns the file path.
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Returns the event int, like 1 for file changed, 2 for file deleted and 3 for file created.
     */
    public function getEvent(): int
    {
        return $this->event;
    }
}
