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
                return str_replace(['/'], DIRECTORY_SEPARATOR, $paths);
            } elseif (is_array($paths)) {
                $newPaths = [];

                foreach ($paths as $path) {
                    $newPaths[] = str_replace(['/'], DIRECTORY_SEPARATOR, $path);
                }

                return $newPaths;
            }
        }

        return $paths;
    }
}
