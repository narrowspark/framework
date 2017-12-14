<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use DirectoryIterator;

abstract class AbstractLoadFiles
{
    /**
     * Get all of the files for the application.
     *
     * @param string $path
     *
     * @return array
     */
    protected function getFiles(string $path): array
    {
        $files = [];
        $dir   = new DirectoryIterator($path);

        foreach ($dir as $fileinfo) {
            if (! $fileinfo->isDot()) {
                $path = $fileinfo->getRealPath();

                $files[\basename($path, '.php')] = $path;
            }
        }

        return $files;
    }
}
