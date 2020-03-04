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

namespace Viserio\Component\Filesystem\Watcher\Resource\Locator;

use SplFileInfo;
use Traversable;
use Viserio\Component\Filesystem\Watcher\Resource\ArrayResource;
use Viserio\Component\Filesystem\Watcher\Resource\DirectoryResource;
use Viserio\Component\Filesystem\Watcher\Resource\FileResource;
use Viserio\Contract\Filesystem\Watcher\Resource as ResourceContract;
use function Viserio\Component\Finder\glob;

/**
 * @internal
 */
final class FileResourceLocator
{
    public static function locate($path): ?ResourceContract
    {
        if ($path instanceof Traversable) {
            $path = \iterator_to_array($path);
        }

        if ($path instanceof SplFileInfo) {
            $realpath = $path->getRealPath();

            $path = $realpath !== false ? $realpath : $path->getPathname();
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

            return new ArrayResource(\array_map(static function (string $path): FileResource {
                return new FileResource($path);
            }, $paths));
        }

        return null;
    }
}
