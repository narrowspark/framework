<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Symfony\Component\Finder\Finder;

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

        foreach (Finder::create()->files()->name('*.php')->in($path) as $file) {
            $files[\basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        return $files;
    }
}
