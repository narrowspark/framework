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

namespace Viserio\Component\Filesystem\Watcher\Resource\Locator;

use SplFileInfo;
use Traversable;
use Viserio\Component\Filesystem\Watcher\Resource\ArrayResource;
use Viserio\Component\Filesystem\Watcher\Resource\DirectoryResource;
use Viserio\Component\Filesystem\Watcher\Resource\FileResource;
use Viserio\Contract\Filesystem\Watcher\Resource as ResourceContract;
use function Viserio\Component\Filesystem\glob;

/**
 * @internal
 */
final class FileResourceLocator
{
    /**
     * @param mixed $path
     *
     * @return null|\Viserio\Contract\Filesystem\Watcher\Resource
     */
    public static function locate($path): ?ResourceContract
    {
        if ($path instanceof Traversable) {
            $path = \iterator_to_array($path);
        }

        if ($path instanceof SplFileInfo) {
            $path = $path->getRealPath() ?: $path->getPathname();
        }

        if (\is_array($path)) {
            return new ArrayResource(\array_map('self::locate', $path));
        }

        if (\is_string($path)) {
            if (\is_dir($path)) {
                return new DirectoryResource($path);
            }

            $paths = glob($path, \defined('GLOB_BRACE') ? \GLOB_BRACE : 0);

            if (\count($paths) === 1) {
                return new FileResource($paths[0]);
            }

            return new ArrayResource(\array_map(static function (string $path) {
                return new FileResource($path);
            }, $paths));
        }

        return null;
    }
}
