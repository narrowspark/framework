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
     * @param array|string $extensions
     *
     * @return array
     */
    protected function getFiles(string $path, $extensions = 'php'): array
    {
        $files = [];
        $dir   = new DirectoryIterator($path);

        foreach ($dir as $fileinfo) {
            if (! $fileinfo->isDot()) {
                $extension = \pathinfo($fileinfo->getRealPath(), PATHINFO_EXTENSION);

                if (\in_array($extension, (array) $extensions, true)) {
                    $path = $fileinfo->getRealPath();
                    $key  = \basename($path, '.' . $extension);

                    if ($key === 'serviceproviders') {
                        continue;
                    }

                    $files[$key] = $path;
                }
            }
        }

        return $files;
    }
}
