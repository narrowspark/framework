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

namespace Viserio\Component\Container\Bootstrap\Cache\Contract;

interface Cache
{
    /**
     * Get cache file path.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Set the patch for the cache file.
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath(string $path): self;

    /**
     * Write content to a cache file.
     *
     * @param string $content The content to write in the cache
     *
     * @return void
     */
    public function write(string $content): void;
}
