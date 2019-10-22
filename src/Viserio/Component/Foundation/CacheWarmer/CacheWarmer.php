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

namespace Viserio\Component\Foundation\CacheWarmer;

use Viserio\Contract\Foundation\CacheWarmer\CacheWarmer as CacheWarmerContract;
use Viserio\Contract\Foundation\Exception\RuntimeException;

abstract class CacheWarmer implements CacheWarmerContract
{
    protected function writeCacheFile(string $file, string $content): void
    {
        $tmpFile = @\tempnam(\dirname($file), \basename($file));

        if (false !== @\file_put_contents($tmpFile, $content) && @\rename($tmpFile, $file)) {
            @\chmod($file, 0666 & ~\umask());

            return;
        }

        throw new RuntimeException(\sprintf('Failed to write cache file "%s".', $file));
    }
}
