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

namespace Viserio\Component\Container\Bootstrap\Cache;

use Viserio\Component\Container\Bootstrap\Cache\Contract\Cache as CacheContract;

final class StreamCache implements CacheContract
{
    /** @var resource */
    private $lock;

    /**
     * Path to the cache file.
     *
     * @var string
     */
    private $path;

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
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(string $path): CacheContract
    {
        $this->path = $path;

        return $this;
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
