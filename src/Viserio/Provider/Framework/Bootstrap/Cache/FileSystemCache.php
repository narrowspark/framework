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

final class FileSystemCache implements CacheContract
{
    /**
     * Path to the cache file.
     *
     * @var string
     */
    private $path;

    /**
     * Create a FileSystemCache instance.
     *
     * @param null|string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
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
        $this->filesystem->write($this->path, $content);

        try {
            $this->filesystem->setVisibility($this->path, 0666, \umask());
        } catch (IOException $e) {
            // discard chmod failure (some filesystem may not support it)
        }

        if (\function_exists('opcache_invalidate') && \filter_var(\ini_get('opcache.enable'), \FILTER_VALIDATE_BOOLEAN)) {
            @\opcache_invalidate($this->path, true);
        }
    }
}
