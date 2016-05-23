<?php
namespace Viserio\Support\Traits;

use LogicException;

trait NormalizePathAndDirectorySeparatorTrait
{
    /**
     * Fix directory separators for windows, linux and normalize path.     *
     *
     * @param string|array $paths
     *
     * @return string|array
     */
    public function normalizeDirectorySeparator($paths)
    {
        if (is_array($paths)) {
            return $this->normalizeAndAddDirectorySeparatorOnArray($paths);
        }

        if (strpos($paths, 'vfs:') !== false) {
            return $paths;
        }

        return $this->normalizePath(str_replace('\\', '/', $paths));
    }

    /**
     * Normalize path.
     *
     * @param string $path
     *
     * @throws \LogicException
     *
     * @return string
     */
    public function normalizePath(string $path): string
    {
        // Remove any kind of funky unicode whitespace
        $normalized = preg_replace('#\p{C}+|^\./#u', '', $path);
        $normalized = $this->normalizeRelativePath($normalized);

        if (preg_match('#/\.{2}|^\.{2}/|^\.{2}$#', $normalized)) {
            throw new LogicException(
                'Path is outside of the defined root, path: [' . $path . '], resolved: [' . $normalized . ']'
            );
        }

        $normalized = preg_replace('#\\\{2,}#', '\\', trim($normalized, '\\'));
        $normalized = preg_replace('#/{2,}#', '/', trim($normalized, '/'));

        return $normalized;
    }

    /**
     * Normalize relative directories in a path.
     *
     * @param string $path
     *
     * @return string
     */
    public function normalizeRelativePath(string $path): string
    {
        // Path remove self referring paths ("/./").
        $path = preg_replace('#/\.(?=/)|^\./|/\./?$#', '', $path);

        // Regex for resolving relative paths
        $regex = '#/*[^/\.]+/\.\.#Uu';

        while (preg_match($regex, $path)) {
            $path = preg_replace($regex, '', $path);
        }

        return $path;
    }

    private function normalizeAndAddDirectorySeparatorOnArray(array $paths)
    {
        $newPaths = [];

        foreach ($paths as $path) {
            if (strpos($path, 'vfs:') !== false) {
                $newPaths[] = $path;
            } else {
                $newPaths[] = $this->normalizePath(str_replace('\\', '/', $path));
            }
        }

        return $newPaths;
    }
}
