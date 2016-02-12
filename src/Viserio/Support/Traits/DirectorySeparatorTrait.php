<?php
namespace Viserio\Support\Traits;

use LogicException;

trait DirectorySeparatorTrait
{
    /**
     * Fix directory separators for windows, linux and normalize path.     *
     *
     * @param string|array $paths
     *
     * @return string|array
     */
    public function getDirectorySeparator($paths)
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            if (is_string($paths)) {
                if (strpos('vfs:', $paths) !== false) {
                    return $this->normalizePath($paths);
                }

                return $this->normalizePath(str_replace('/', DIRECTORY_SEPARATOR, $paths));
            } elseif (is_array($paths)) {
                $newPaths = [];

                foreach ($paths as $path) {
                    if (strpos('vfs:', $path) !== false) {
                        $newPaths[] = $this->normalizePath($path);
                    }

                    $newPaths[] = $this->normalizePath(str_replace('/', DIRECTORY_SEPARATOR, $path));
                }

                return $newPaths;
            }
        }

        if (is_array($paths)) {
            $normalizedPaths = [];

            foreach ($paths as $path) {
                $normalizedPaths[] = $this->normalizePath($path);
            }

            return $normalizedPaths;
        }

        return $this->normalizePath($paths);
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
    public function normalizePath($path)
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
    public function normalizeRelativePath($path)
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
}
