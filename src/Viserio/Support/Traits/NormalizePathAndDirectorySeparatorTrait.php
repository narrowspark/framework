<?php
declare(strict_types=1);
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
    public static function normalizeDirectorySeparator($paths)
    {
        if (is_array($paths)) {
            return self::normalizeAndAddDirectorySeparatorOnArray($paths);
        }

        if (is_string($paths) && strpos($paths, 'vfs:') !== false) {
            return $paths;
        }

        return str_replace('\\', '/', $paths);
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
    public static function normalizePath(string $path): string
    {
        // Remove any kind of funky unicode whitespace
        $normalized = preg_replace('#\p{C}+|^\./#u', '', $path);
        $normalized = self::normalizeRelativePath($normalized);

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
    public static function normalizeRelativePath(string $path): string
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

    /**
     * Normalize path.
     *
     * @param array $paths
     *
     * @return array
     */
    private static function normalizeAndAddDirectorySeparatorOnArray(array $paths): array
    {
        $newPaths = [];

        foreach ($paths as $path) {
            if (strpos($path, 'vfs:') !== false) {
                $newPaths[] = $path;
            } else {
                $newPaths[] = self::normalizePath(str_replace('\\', '/', $path));
            }
        }

        return $newPaths;
    }
}
