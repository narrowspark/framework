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

namespace Viserio\Component\Filesystem\Tests\Traits\Fixture;

use Viserio\Component\Filesystem\Traits\FilesystemHelperTrait;

class FilesystemHelperTraitClass
{
    use FilesystemHelperTrait;

    public function has(string $path): bool
    {
        return \file_exists($path);
    }

    public function isDirectory(string $dirname): bool
    {
        return \is_dir($dirname);
    }

    /**
     * Get normalize or prefixed path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getTransformedPath(string $path): string
    {
        if (isset($this->driver)) {
            $prefix = \method_exists($this->driver, 'getPathPrefix') ? $this->driver->getPathPrefix() : '';

            return $prefix . $path;
        }

        return $path;
    }
}
