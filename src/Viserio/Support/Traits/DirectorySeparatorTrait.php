<?php
namespace Viserio\Support\Traits;

trait DirectorySeparatorTrait
{
    /**
     * Fix directory separators for windows and linux
     *
     * @param string|array $paths
     *
     * @return string|array
     */
    protected function getDirectorySeparator($paths)
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            if (is_string($paths)) {
                if (preg_match('/vfs:/', $paths)) {
                    return $paths;
                }

                return str_replace(['/'], DIRECTORY_SEPARATOR, $paths);
            } elseif (is_array($paths)) {
                $newPaths = [];

                foreach ($paths as $path) {
                    if (preg_match('/vfs:/', $path)) {
                        $newPaths[] = $path;
                    }

                    $newPaths[] = str_replace(['/'], DIRECTORY_SEPARATOR, $path);
                }

                return $newPaths;
            }
        }

        return $paths;
    }
}
