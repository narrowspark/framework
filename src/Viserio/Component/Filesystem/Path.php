<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem;

use Viserio\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Contract\Filesystem\Exception\RuntimeException;

/**
 * Contains utility methods for handling path strings.
 *
 * The methods in this class are able to deal with both UNIX and Windows paths
 * with both forward and backward slashes. All methods return normalized parts
 * containing only forward slashes and no excess "." and ".." segments.
 *
 * Based on the webmozart path-util package
 *
 * @see https://github.com/filesystem/path-util/blob/master/src/Path.php
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Thomas Schulz <mail@king2500.net>
 */
final class Path
{
    /**
     * The number of buffer entries that triggers a cleanup operation.
     */
    public const CLEANUP_THRESHOLD = 1250;
    /**
     * The buffer size after the cleanup operation.
     */
    public const CLEANUP_SIZE = 1000;

    /**
     * Buffers input/output of {@link canonicalize()}.
     *
     * @var array
     */
    private static $buffer = [];

    /** @var int */
    private static $bufferSize = 0;

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Canonicalizes the given path.
     *
     * During normalization, all slashes are replaced by forward slashes ("/").
     * Furthermore, all "." and ".." segments are removed as far as possible.
     * ".." segments at the beginning of relative paths are not removed.
     *
     * ```php
     * echo Path::canonicalize("\filesystem\puli\..\css\style.css");
     * // => /filesystem/css/style.css
     *
     * echo Path::canonicalize("../css/./style.css");
     * // => ../css/style.css
     * ```
     *
     * This method is able to deal with both UNIX and Windows paths.
     *
     * @param string $path
     *
     * @return string
     */
    public static function canonicalize(string $path): string
    {
        if ($path === '') {
            return '';
        }

        // This method is called by many other methods in this class. Buffer
        // the canonicalized paths to make up for the severe performance
        // decrease.
        if (isset(self::$buffer[$path])) {
            return self::$buffer[$path];
        }

        // Replace "~" with user's home directory.
        $path = (string) \preg_replace_callback(
            '~^\~(?<user>[^/\s]+?)?(?=/|$)~',
            static function (array $matches) {
                return self::getHomeDirectory($matches['user'] ?? null);
            },
            $path
        );

        $path = \str_replace('\\', '/', $path);

        [$root, $pathWithoutRoot] = self::split($path);

        $parts = \explode('/', $pathWithoutRoot);
        $canonicalParts = [];

        // Collapse "." and "..", if possible
        foreach ($parts as $part) {
            if ($part === '.' || $part === '') {
                continue;
            }

            // Collapse ".." with the previous part, if one exists
            // Don't collapse ".." if the previous part is also ".."
            if ($part === '..' && \count($canonicalParts) > 0 && $canonicalParts[\count($canonicalParts) - 1] !== '..') {
                \array_pop($canonicalParts);

                continue;
            }

            // Only add ".." prefixes for relative paths
            if ($part !== '..' || $root === '') {
                $canonicalParts[] = $part;
            }
        }

        // Add the root directory again
        self::$buffer[$path] = $canonicalPath = $root . \implode('/', $canonicalParts);
        self::$bufferSize++;

        // Clean up regularly to prevent memory leaks
        if (self::$bufferSize > self::CLEANUP_THRESHOLD) {
            self::$buffer = \array_slice(self::$buffer, -self::CLEANUP_SIZE, null, true);
            self::$bufferSize = self::CLEANUP_SIZE;
        }

        return $canonicalPath;
    }

    /**
     * Normalizes the given path.
     *
     * During normalization, all slashes are replaced by forward slashes ("/").
     * Contrary to {@link canonicalize()}, this method does not remove invalid
     * or dot path segments. Consequently, it is much more efficient and should
     * be used whenever the given path is known to be a valid, absolute system
     * path.
     *
     * This method is able to deal with both UNIX and Windows paths.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalize(string $path): string
    {
        return \str_replace('\\', '/', $path);
    }

    /**
     * Returns the directory part of the path.
     *
     * This method is similar to PHP's dirname(), but handles various cases
     * where dirname() returns a weird result:
     *
     *  - dirname() does not accept backslashes on UNIX
     *  - dirname("C:/filesystem") returns "C:", not "C:/"
     *  - dirname("C:/") returns ".", not "C:/"
     *  - dirname("C:") returns ".", not "C:/"
     *  - dirname("filesystem") returns ".", not ""
     *  - dirname() does not canonicalize the result
     *
     * This method fixes these shortcomings and behaves like dirname()
     * otherwise.
     *
     * The result is a canonical path.
     *
     * @param string $path
     *
     * @return string The canonical directory part. Returns the root directory
     *                if the root directory is passed. Returns an empty string
     *                if a relative path is passed that contains no slashes.
     *                Returns an empty string if an empty string is passed.
     */
    public static function getDirectory(string $path): string
    {
        if ($path === '') {
            return '';
        }

        $path = self::canonicalize($path);

        // Maintain scheme
        if (false !== ($pos = \strpos($path, '://'))) {
            $scheme = \substr($path, 0, $pos + 3);
            $path = \substr($path, $pos + 3);
        } else {
            $scheme = '';
        }

        if (false !== ($pos = \strrpos($path, '/'))) {
            // Directory equals root directory "/"
            if (0 === $pos) {
                return $scheme . '/';
            }

            // Directory equals Windows root "C:/"
            if ($pos === 2 && $path[1] === ':' && \ctype_alpha($path[0])) {
                return $scheme . \substr($path, 0, 3);
            }

            return $scheme . \substr($path, 0, $pos);
        }

        return '';
    }

    /**
     * Returns canonical path of the user's home directory.
     *
     * Supported operating systems:
     *
     *  - UNIX
     *  - Windows8 and upper
     *
     * If your operation system or environment isn't supported, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @param null|string $user
     *
     * @throws \Viserio\Contract\Filesystem\Exception\RuntimeException If your operation system or environment isn't supported
     *
     * @return string
     */
    public static function getHomeDirectory(?string $user = null): string
    {
        $home = null;

        // For UNIX support
        if (\is_string($envHome = \getenv('HOME')) && $envHome !== '') {
            $home = self::canonicalize((string) $envHome);
        }

        // For >= Windows8 support
        if (\is_string($envHomeDrive = \getenv('HOMEDRIVE')) && $envHomeDrive !== '' && \is_string($envHomePath = \getenv('HOMEPATH')) && $envHomePath !== '') {
            $home = self::canonicalize($envHomeDrive . $envHomePath);
        }

        if ($home === null) {
            throw new RuntimeException("Cannot find the home directory path: Your environment or operation system isn't supported.");
        }

        if ($user !== '' && $user !== null) {
            $home = self::join(self::getDirectory($home), $user);
        }

        return $home;
    }

    /**
     * Returns the root directory of a path.
     *
     * The result is a canonical path.
     *
     * @param string $path
     *
     * @return string The canonical root directory. Returns an empty string if
     *                the given path is relative or empty.
     */
    public static function getRoot(string $path): string
    {
        if ($path === '') {
            return '';
        }

        // Maintain scheme
        if (false !== ($pos = \strpos($path, '://'))) {
            $scheme = \substr($path, 0, $pos + 3);
            $path = \substr($path, $pos + 3);
        } else {
            $scheme = '';
        }
        // UNIX root "/" or "\" (Windows style)
        if (\strpos($path, '/') === 0 || '\\' === $path[0]) {
            return $scheme . '/';
        }
        $length = \strlen($path);
        // Windows root
        if ($length > 1 && $path[1] === ':' && \ctype_alpha($path[0])) {
            // Special case: "C:"
            if (2 === $length) {
                return $scheme . $path . '/';
            }

            // Normal case: "C:/ or "C:\"
            if ($path[2] === '/' || $path[2] === '\\') {
                return $scheme . $path[0] . $path[1] . '/';
            }
        }

        return '';
    }

    /**
     * Returns the file name without the extension from a file path.
     *
     * @param string      $path
     * @param null|string $extension if specified, only that extension is cut
     *                               off (may contain leading dot)
     *
     * @return string
     */
    public static function getFilenameWithoutExtension(string $path, ?string $extension = null): string
    {
        if ($path === '') {
            return '';
        }

        if ($extension !== null) {
            // remove extension and trailing dot
            return \rtrim(\basename($path, $extension), '.');
        }

        return \pathinfo($path, \PATHINFO_FILENAME);
    }

    /**
     * Returns the extension from a file path (without leading dot).
     *
     * @param string $path
     * @param bool   $forceLowerCase forces the extension to be lower-case
     *
     * @return string
     */
    public static function getExtension(string $path, bool $forceLowerCase = false): string
    {
        if ($path === '') {
            return '';
        }

        $extension = \pathinfo($path, \PATHINFO_EXTENSION);

        if ($forceLowerCase) {
            $extension = self::strToLower($extension);
        }

        return $extension;
    }

    /**
     * Returns whether the path has an (or the specified) extension.
     *
     * @param string               $path       the path string
     * @param null|string|string[] $extensions if null or not provided, checks if
     *                                         an extension exists, otherwise
     *                                         checks for the specified extension
     *                                         or array of extensions (with or
     *                                         without leading dot)
     * @param bool                 $ignoreCase whether to ignore case-sensitivity
     *
     * @return bool
     */
    public static function hasExtension(string $path, $extensions = null, bool $ignoreCase = false): bool
    {
        if ($path === '') {
            return false;
        }

        $actualExtension = self::getExtension($path, $ignoreCase);

        // Only check if path has any extension
        if ($extensions === [] || $extensions === null) {
            return '' !== $actualExtension;
        }

        if (\is_string($extensions)) {
            $extensions = [$extensions];
        }

        foreach ($extensions as $key => $extension) {
            if ($ignoreCase) {
                $extension = self::strToLower($extension);
            }

            // remove leading '.' in extensions array
            $extensions[$key] = \ltrim($extension, '.');
        }

        return \in_array($actualExtension, $extensions, true);
    }

    /**
     * Changes the extension of a path string.
     *
     * @param string $path      The path string with filename.ext to change.
     * @param string $extension new extension (with or without leading dot)
     *
     * @return string the path string with new file extension
     */
    public static function changeExtension(string $path, string $extension): string
    {
        if ($path === '') {
            return '';
        }

        $actualExtension = self::getExtension($path);
        $extension = \ltrim($extension, '.');

        // No extension for paths
        if (\substr($path, -1) === '/') {
            return $path;
        }

        // No actual extension in path
        if ($actualExtension === '') {
            return $path . ('.' === \substr($path, -1) ? '' : '.') . $extension;
        }

        return \substr($path, 0, -\strlen($actualExtension)) . $extension;
    }

    /**
     * Check if the path is a absolute path.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isAbsolute(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        // Strip scheme
        if (false !== ($pos = \strpos($path, '://'))) {
            $path = \substr($path, $pos + 3);
        }

        // UNIX root "/" or "\" (Windows style)
        if (\strpos($path, '/') === 0 || $path[0] === '\\') {
            return true;
        }

        // Windows root
        if (\strlen($path) > 1 && $path[1] === ':' && \ctype_alpha($path[0])) {
            // Special case: "C:"
            if (\strlen($path) === 2) {
                return true;
            }

            // Normal case: "C:/ or "C:\"
            if ($path[2] === '/' || $path[2] === '\\') {
                return true;
            }
        }

        return false;
    }

    /*
     * Check if the path is a relative path.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isRelative(string $path): bool
    {
        return ! self::isAbsolute($path);
    }

    /**
     * Turns a relative path into an absolute path in canonical form.
     *
     * Usually, the relative path is appended to the given base path. Dot
     * segments ("." and "..") are removed/collapsed and all slashes turned
     * into forward slashes.
     *
     * ```php
     * echo Path::makeAbsolute("../style.css", "/filesystem/puli/css");
     * // => /filesystem/puli/style.css
     * ```
     *
     * If an absolute path is passed, that path is returned unless its root
     * directory is different than the one of the base path. In that case, an
     * exception is thrown.
     *
     * ```php
     * Path::makeAbsolute("/style.css", "/filesystem/puli/css");
     * // => /style.css
     *
     * Path::makeAbsolute("C:/style.css", "C:/filesystem/puli/css");
     * // => C:/style.css
     *
     * Path::makeAbsolute("C:/style.css", "/filesystem/puli/css");
     * // \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     * ```
     *
     * If the base path is not an absolute path, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @param string $path
     * @param string $basePath an absolute base path
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException if the base path is not absolute or if
     *                                                                         the given path is an absolute path with
     *                                                                         a different root than the base path&& $path[1] === ':'
     *
     * @return string
     */
    public static function makeAbsolute(string $path, string $basePath): string
    {
        if ($basePath === '') {
            throw new InvalidArgumentException(\sprintf('The base path must be a non-empty string. Got: [%s]', $basePath));
        }

        if (! self::isAbsolute($basePath)) {
            throw new InvalidArgumentException(\sprintf('The base path [%s] is not an absolute path.', $basePath));
        }

        if (self::isAbsolute($path)) {
            return self::canonicalize($path);
        }

        if (false !== ($pos = \strpos($basePath, '://'))) {
            $scheme = \substr($basePath, 0, $pos + 3);
            $basePath = \substr($basePath, $pos + 3);
        } else {
            $scheme = '';
        }

        return $scheme . self::canonicalize(\rtrim($basePath, '/\\') . '/' . $path);
    }

    /**
     * Turns a path into a relative path.
     *
     * The relative path is created relative to the given base path:
     *
     * ```php
     * echo Path::makeRelative("/filesystem/style.css", "/filesystem/puli");
     * // => ../style.css
     * ```
     *
     * If a relative path is passed and the base path is absolute, the relative
     * path is returned unchanged:
     *
     * ```php
     * Path::makeRelative("style.css", "/filesystem/puli/css");
     * // => style.css
     * ```
     *
     * If both paths are relative, the relative path is created with the
     * assumption that both paths are relative to the same directory:
     *
     * ```php
     * Path::makeRelative("style.css", "filesystem/puli/css");
     * // => ../../../style.css
     * ```
     *
     * If both paths are absolute, their root directory must be the same,
     * otherwise an exception is thrown:
     *
     * ```php
     * Path::makeRelative("C:/filesystem/style.css", "/filesystem/puli");
     * // \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     * ```
     *
     * If the passed path is absolute, but the base path is not, an exception
     * is thrown as well:
     *
     * ```php
     * Path::makeRelative("/filesystem/style.css", "filesystem/puli");
     * // \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     * ```
     *
     * If the base path is not an absolute path, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @param string $path
     * @param string $basePath
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException if the base path is not absolute or if
     *                                                                         the given path has a different root
     *                                                                         than the base path
     *
     * @return string
     */
    public static function makeRelative(string $path, string $basePath): string
    {
        [$root, $relativePath] = self::split(self::canonicalize($path));
        [$baseRoot, $relativeBasePath] = self::split(self::canonicalize($basePath));

        // If the base path is given as absolute path and the path is already
        // relative, consider it to be relative to the given absolute path
        // already
        if ($root === '' && $baseRoot !== '') {
            // If base path is already in its root
            if ($relativeBasePath === '') {
                $relativePath = \ltrim($relativePath, './\\');
            }

            return $relativePath;
        }

        // If the passed path is absolute, but the base path is not, we
        // cannot generate a relative path
        if ($root !== '' && $baseRoot === '') {
            throw new InvalidArgumentException(\sprintf('The absolute path [%s] cannot be made relative to the relative path [%s]. You should provide an absolute base path instead.', $path, $basePath));
        }

        // Fail if the roots of the two paths are different
        if ($baseRoot !== '' && $root !== $baseRoot) {
            throw new InvalidArgumentException(\sprintf('The path [%s] cannot be made relative to [%s], because they have different roots ([%s] and [%s]).', $path, $basePath, $root, $baseRoot));
        }

        if ('' === $relativeBasePath) {
            return $relativePath;
        }
        // Build a "../../" prefix with as many "../" parts as necessary
        $parts = \explode('/', $relativePath);
        $baseParts = \explode('/', $relativeBasePath);
        $dotDotPrefix = '';
        // Once we found a non-matching part in the prefix, we need to add
        // "../" parts for all remaining parts
        $match = true;

        foreach ($baseParts as $i => $basePart) {
            if ($match && isset($parts[$i]) && $basePart === $parts[$i]) {
                unset($parts[$i]);

                continue;
            }
            $match = false;
            $dotDotPrefix .= '../';
        }

        return \rtrim($dotDotPrefix . \implode('/', $parts), '/');
    }

    /**
     * Returns whether the given path is on the local filesystem.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isLocal(string $path): bool
    {
        return $path !== '' && \strpos($path, '://') === false;
    }

    /**
     * Returns the longest common base path in canonical form of a set of paths or
     * `null` if the paths are on different Windows partitions.
     *
     * Dot segments ("." and "..") are removed/collapsed and all slashes turned
     * into forward slashes.
     *
     * ```php
     * $basePath = Path::getLongestCommonBasePath([
     *     '/filesystem/css/style.css',
     *     '/filesystem/css/..'
     * ]);
     * // => /filesystem
     * ```
     *
     * The root is returned if no common base path can be found:
     *
     * ```php
     * $basePath = Path::getLongestCommonBasePath([
     *     '/filesystem/css/style.css',
     *     '/puli/css/..'
     * ]);
     * // => /
     * ```
     *
     * If the paths are located on different Windows partitions, `null` is
     * returned.
     *
     * ```php
     * $basePath = Path::getLongestCommonBasePath([
     *     'C:/filesystem/css/style.css',
     *     'D:/filesystem/css/..'
     * ]);
     * // => null
     * ```
     *
     * @param string[] $paths
     *
     * @return null|string
     */
    public static function getLongestCommonBasePath(string ...$paths): ?string
    {
        [$bpRoot, $basePath] = self::split(self::canonicalize((string) \reset($paths)));

        for (\next($paths); \key($paths) !== null && $basePath !== ''; \next($paths)) {
            [$root, $path] = self::split(self::canonicalize(\current($paths)));
            // If we deal with different roots (e.g. C:/ vs. D:/), it's time
            // to quit
            if ($root !== $bpRoot) {
                return null;
            }

            // Make the base path shorter until it fits into path
            while (true) {
                if ($basePath === '.') {
                    // No more base paths
                    $basePath = '';
                    // Next path
                    continue 2;
                }

                // Prevent false positives for common prefixes
                // see isBasePath()
                if (0 === \strpos($path . '/', $basePath . '/')) {
                    // Next path
                    continue 2;
                }
                $basePath = \dirname($basePath);
            }
        }

        return $bpRoot . $basePath;
    }

    /**
     * Joins two or more path strings into a canonical path.
     *
     * @param string[] $paths
     *
     * @return string
     */
    public static function join(string ...$paths): string
    {
        $finalPath = null;
        $wasScheme = false;

        foreach ($paths as $path) {
            $path = $path;

            if ($path === '') {
                continue;
            }

            if (null === $finalPath) {
                // For first part we keep slashes, like '/top', 'C:\' or 'phar://'
                $finalPath = $path;
                $wasScheme = (false !== \strpos($path, '://'));

                continue;
            }

            // Only add slash if previous part didn't end with '/' or '\'
            if (! \in_array(\substr($finalPath, -1), ['/', '\\'], true)) {
                $finalPath .= '/';
            }
            // If first part included a scheme like 'phar://' we allow current part to start with '/', otherwise trim
            $finalPath .= $wasScheme ? $path : \ltrim($path, '/');
            $wasScheme = false;
        }

        if ($finalPath === null) {
            return '';
        }

        return self::canonicalize($finalPath);
    }

    /**
     * Returns whether a path is a base path of another path.
     *
     * Dot segments ("." and "..") are removed/collapsed and all slashes turned
     * into forward slashes.
     *
     * ```php
     * Path::isBasePath('/filesystem', '/filesystem/css');
     * // => true
     *
     * Path::isBasePath('/filesystem', '/filesystem');
     * // => true
     *
     * Path::isBasePath('/filesystem', '/filesystem/..');
     * // => false
     *
     * Path::isBasePath('/filesystem', '/puli');
     * // => false
     * ```
     *
     * @param string $basePath
     * @param string $ofPath
     *
     * @return bool
     */
    public static function isBasePath(string $basePath, string $ofPath): bool
    {
        // Append slashes to prevent false positives when two paths have
        // a common prefix, for example /base/foo and /base/foobar.
        // Don't append a slash for the root "/", because then that root
        // won't be discovered as common prefix ("//" is not a prefix of
        // "/foobar/").
        return \strpos(self::canonicalize($ofPath) . '/', \rtrim(self::canonicalize($basePath), '/') . '/') === 0;
    }

    /**
     * Splits a canonical path into its root directory and the remainder.
     *
     * If the path has no root directory, an empty root directory will be
     * returned.
     *
     * If the root directory is a Windows style partition, the resulting root
     * will always contain a trailing slash.
     *
     * list ($root, $path) = Path::split("C:/filesystem")
     * // => ["C:/", "filesystem"]
     *
     * list ($root, $path) = Path::split("C:")
     * // => ["C:/", ""]
     *
     * @param string $path
     *
     * @return string[] an array with the root directory and the remaining
     *                  relative path
     */
    private static function split(string $path): array
    {
        if ($path === '') {
            return ['', ''];
        }

        // Remember scheme as part of the root, if any
        if (false !== ($pos = \strpos($path, '://'))) {
            $root = \substr($path, 0, $pos + 3);
            $path = \substr($path, $pos + 3);
        } else {
            $root = '';
        }

        $length = \strlen($path);

        // Remove and remember root directory
        if (\strpos($path, '/') === 0) {
            $root .= '/';
            $path = $length > 1 ? \substr($path, 1) : '';
        } elseif ($length > 1 && $path[1] === ':' && \ctype_alpha($path[0])) {
            if (2 === $length) {
                // Windows special case: "C:"
                $root .= $path . '/';
                $path = '';
            } elseif ($path[2] === '/') {
                // Windows normal case: "C:/"..
                $root .= \substr($path, 0, 3);
                $path = $length > 3 ? \substr($path, 3) : '';
            }
        }

        return [$root, $path];
    }

    /**
     * Returns a lower case string.
     *
     * @param string $str
     *
     * @return string
     */
    private static function strToLower(string $str): string
    {
        return \mb_strtolower($str, (string) \mb_detect_encoding($str, \mb_detect_order(), true));
    }
}
