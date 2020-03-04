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

namespace Viserio\Provider\Framework\Bootstrap\Cache;

final class FileSystemCache extends AbstractCache
{
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
