<?php
namespace Viserio\Filesystem;

use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;

class ClassFinder
{
    /**
     * Create a new class loader.
     *
     * @param FilesystemContract $files
     */
    public function __construct(FilesystemContract $files)
    {
        $this->files = $files;
    }

    /**
     * Find all the class and interface names in a given directory.
     *
     * @param string $directory
     *
     * @return array
     */
    public function findClasses($directory)
    {
        $classes = [];

        foreach ($this->files->files($directory) as $file) {
            if ($this->files->getExtension($file) === 'php') {
                $classes[] = $this->findClass($file);
            }
        }

        return array_filter($classes);
    }

    /**
     * Extract the class name from the file at the given path.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function findClass($path)
    {
        $namespace = null;
        $tokens = token_get_all(file_get_contents($path));

        foreach ($tokens as $key => $token) {
        }
    }
}
