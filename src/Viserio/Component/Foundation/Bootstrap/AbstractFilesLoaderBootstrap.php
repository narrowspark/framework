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

namespace Viserio\Component\Foundation\Bootstrap;

use DirectoryIterator;

abstract class AbstractFilesLoaderBootstrap
{
    /**
     * Bypass given files by key.
     *
     * @var array
     */
    protected static $bypassFiles = [];

    /**
     * Get all of the files for the application.
     *
     * @param string       $path
     * @param array|string $extensions
     *
     * @return array
     */
    protected static function getFiles(string $path, $extensions = 'php'): array
    {
        if (! \file_exists($path)) {
            return [];
        }

        $files = [];
        $dir = new DirectoryIterator($path);

        foreach ($dir as $fileinfo) {
            if (! $fileinfo->isDot()) {
                $extension = \pathinfo($fileinfo->getRealPath(), \PATHINFO_EXTENSION);

                if (\in_array($extension, (array) $extensions, true)) {
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
