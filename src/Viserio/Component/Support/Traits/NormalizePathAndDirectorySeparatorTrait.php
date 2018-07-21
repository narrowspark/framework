<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Traits;

use Viserio\Component\Contract\Support\Exception\LogicException;

trait NormalizePathAndDirectorySeparatorTrait
{
    /**
     * Fix directory separators for windows, linux and normalize path.
     *
     * @param array|string $paths
     *
     * @return array|string
     */
    protected static function normalizeDirectorySeparator($paths)
    {
        if (\is_array($paths)) {
            return self::normalizeAndAddDirectorySeparatorOnArray($paths);
        }

        if (\is_string($paths) && \mb_strpos($paths, 'vfs:') !== false) {
            return $paths;
        }

        return \str_replace('\\', '/', $paths);
    }

    /**
     * Normalize path.
     *
     * @param string $path
     *
     * @throws \Viserio\Component\Contract\Support\Exception\LogicException
     *
     * @return string
     */
    protected static function normalizePath(string $path): string
    {
        // Remove any kind of funky unicode whitespace
        $normalized = \preg_replace('#\p{C}+|^\./#u', '', $path);
        $normalized = self::normalizeRelativePath($normalized);

        if (\preg_match('#/\.{2}|^\.{2}/|^\.{2}$#', $normalized) === 1) {
            throw new LogicException(
                'Path is outside of the defined root, path: [' . $path . '], resolved: [' . $normalized . ']'
            );
        }

        $normalized = \preg_replace('#\\\{2,}#', '\\', \trim($normalized, '\\'));

        return \preg_replace('#/{2,}#', '/', \trim($normalized, '/'));
    }

    /**
     * Normalize relative directories in a path.
     *
     * @param string $path
     *
     * @return string
     */
    protected static function normalizeRelativePath(string $path): string
    {
        // Path remove self referring paths ("/./").
        $path = \preg_replace('#/\.(?=/)|^\./|/\./?$#', '', $path);

        // Regex for resolving relative paths
        $regex = '#/*[^/\.]+/\.\.#Uu';

        while (\preg_match($regex, $path)) {
            $path = \preg_replace($regex, '', $path);
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
            if (\mb_strpos($path, 'vfs:') !== false) {
                $newPaths[] = $path;
            } else {
                $newPaths[] = self::normalizePath(\str_replace('\\', '/', $path));
            }
        }

        return $newPaths;
    }
}
