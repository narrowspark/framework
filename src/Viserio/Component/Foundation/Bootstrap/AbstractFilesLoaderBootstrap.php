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

namespace Viserio\Component\Foundation\Bootstrap;

use DirectoryIterator;

abstract class AbstractFilesLoaderBootstrap
{
    /**
     * Bypass given files by key.
     *
     * @var array<int, string>
     */
    protected static array $bypassFiles = [];

    /**
     * Get all of the files for the application.
     *
     * @param array<int, string> $extensions
     */
    protected static function getFiles(string $path, array $extensions = ['php']): array
    {
        if (! \file_exists($path)) {
            return [];
        }

        $files = [];
        $dir = new DirectoryIterator($path);

        foreach ($dir as $fileinfo) {
            if (! $fileinfo->isDot()) {
                $extension = \pathinfo($fileinfo->getRealPath(), \PATHINFO_EXTENSION);

                if (\in_array($extension, $extensions, true)) {
                    $filePath = $fileinfo->getRealPath();
                    $key = \basename($filePath, '.' . $extension);

                    if (\in_array($key, static::$bypassFiles, true)) {
                        continue;
                    }

                    $files[$key] = $filePath;
                }
            }
        }

        \ksort($files, \SORT_NATURAL);

        return $files;
    }
}
