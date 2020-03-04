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

abstract class AbstractCache
{
    /**
     * Path to the cache file.
     *
     * @var string
     */
    protected $path;

    /**
     * Get cache file path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the patch for the cache file.
     *
     * @return static
     */
    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Write content to a cache file.
     *
     * @param string $content The content to write in the cache
     */
    abstract public function write(string $content): void;
}
