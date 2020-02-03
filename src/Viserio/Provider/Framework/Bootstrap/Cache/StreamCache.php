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

namespace Viserio\Provider\Framework\Bootstrap\Cache;

final class StreamCache extends AbstractCache
{
    /** @var resource */
    private $lock;

    /**
     * Create a StreamCache instance.
     *
     * @param string   $path
     * @param resource $lock
     */
    public function __construct(string $path, $lock)
    {
        $this->path = $path;
        $this->lock = $lock;
    }

    public function __destruct()
    {
        \flock($this->lock, \LOCK_UN);
        \fclose($this->lock);
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $content): void
    {
        \rewind($this->lock);
        \ftruncate($this->lock, 0);
        \fwrite($this->lock, $content);

        if (\function_exists('opcache_invalidate') && \filter_var(\ini_get('opcache.enable'), \FILTER_VALIDATE_BOOLEAN)) {
            \opcache_invalidate($this->path, true);
        }
    }
}
