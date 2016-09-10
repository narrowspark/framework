<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
            $nesting = $this->getNesting($file, $path);
            $files[$nesting . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        return $files;
    }

    /**
     * Get the file nesting path.
     *
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @param string                                $path
     *
     * @return string
     */
    protected function getNesting(SplFileInfo $file, string $path): string
    {
        $directory = dirname($file->getRealPath());

        if ($tree = trim(str_replace($path, '', $directory), DIRECTORY_SEPARATOR)) {
            $tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree) . '.';
        }

        return $tree;
    }
}
