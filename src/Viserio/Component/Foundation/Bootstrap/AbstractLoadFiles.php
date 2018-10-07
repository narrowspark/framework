<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use DirectoryIterator;

abstract class AbstractLoadFiles
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
        $dir   = new DirectoryIterator($path);

        foreach ($dir as $fileinfo) {
            if (! $fileinfo->isDot()) {
                $extension = \pathinfo($fileinfo->getRealPath(), \PATHINFO_EXTENSION);

                if (\in_array($extension, (array) $extensions, true)) {
                    $path = $fileinfo->getRealPath();
                    $key  = \basename($path, '.' . $extension);

                    if (\in_array($key, static::$bypassFiles, true)) {
                        continue;
                    }

                    $files[$key] = $path;
                }
            }
        }

        \ksort($files, \SORT_NATURAL);

        return $files;
    }
}
