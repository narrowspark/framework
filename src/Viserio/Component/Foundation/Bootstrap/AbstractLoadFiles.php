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
        $foundFiles = (array) Finder::create()->files()->name('*.php')->in($path);

        foreach ($foundFiles as $file) {
            $path = $file->getRealPath();

            $files[\basename($path, '.php')] = $path;
        }

        return $files;
    }
}
