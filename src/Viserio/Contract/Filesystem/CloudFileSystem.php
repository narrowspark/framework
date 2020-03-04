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

namespace Viserio\Contract\Filesystem;

interface CloudFileSystem extends Filesystem
{
    /**
     * Get the URL for the file at the given path.
     */
    public function url(string $path): string;
}
