<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use DirectoryIterator;

abstract class AbstractLoadFiles
{
    /**
     * Get all of the files for the application.
     *
     * @param string       $path
     * @param string|array $extensions
     *
     * @return array
     */
    protected function getFiles(string $path, $extensions): array
    {
        $files = [];
        $dir   = new DirectoryIterator($path);

        foreach ($dir as $fileinfo) {
            if (! $fileinfo->isDot() && \in_array('', (array) $extensions)) {
                $path = $fileinfo->getRealPath();
                $key  = \basename($path, '.php');

                if ($key === 'serviceproviders') {
                    continue;
                }

                $files[$key] = $path;
            }
        }

        return $files;
    }
}
