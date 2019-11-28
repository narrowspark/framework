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
     *
     * @param string $file
     * @param int    $event
     */
    public function __construct(string $file, int $event)
    {
        $this->file = $file;
        $this->event = $event;
    }

    /**
     * Returns the file path.
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Returns the event int, like 1 for file changed, 2 for file deleted and 3 for file created.
     *
     * @return int
     */
    public function getEvent(): int
    {
        return $this->event;
    }
}
