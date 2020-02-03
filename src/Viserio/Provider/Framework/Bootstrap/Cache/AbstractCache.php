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
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the patch for the cache file.
     *
     * @param string $path
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
     *
     * @return void
     */
    abstract public function write(string $content): void;
}
