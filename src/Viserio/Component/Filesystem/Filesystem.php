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

use DateTime;
use EmptyIterator;
use FilesystemIterator;
use Generator;
use Iterator;
use Narrowspark\MimeType\MimeTypeFileExtensionGuesser;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;
use Traversable;
use Viserio\Component\Filesystem\Iterator\SplFileInfoMethodFilterIterator;
use Viserio\Component\Filesystem\Watcher\FileChangeWatcher;
use Viserio\Component\Filesystem\Watcher\INotifyWatcher;
use Viserio\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Viserio\Contract\Filesystem\Exception\IOException;
use Viserio\Contract\Filesystem\Exception\NotFoundException;
use Viserio\Contract\Filesystem\Exception\NotSupportedException;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contract\Filesystem\LinkSystem as LinkSystemContract;
use Viserio\Contract\Filesystem\Watcher\Watcher as WatcherContract;

class Filesystem implements FilesystemContract, LinkSystemContract, WatcherContract
{
    /**
     * List of the default permissions for file end directory.
     *
     * @var array<string, array<string, float|int|string>>
     */
    protected static $permissions;

    /**
     * Last catch error message.
     *
     * @var null|string
     */
    private static $lastError;

    /**
     * Last catch error type.
     *
     * @var null|int
     */
    private static $lastType;

    /**
     * Create a new Filesystem instance.
     *
     * @param array<string, array<string, float|int|string>> $permissions
     */
    public function __construct(array $permissions = [])
    {
        self::$permissions = \array_replace_recursive([
            'file' => [
                FilesystemContract::VISIBILITY_PUBLIC => Permissions::notation('0644'),
                FilesystemContract::VISIBILITY_PRIVATE => Permissions::notation('0600'),
            ],
            'dir' => [
                FilesystemContract::VISIBILITY_PUBLIC => Permissions::notation('0755'),
                FilesystemContract::VISIBILITY_PRIVATE => Permissions::notation('0700'),
            ],
        ], $permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $path): bool
    {
        $maxPathLength = \PHP_MAXPATHLEN - 2;

        if (\strlen($path) > $maxPathLength) {
            throw new IOException(\sprintf('Could not check if file exist because path length exceeds %d characters.', $maxPathLength), 0, null, $path);
        }

        if (! file_exists($path)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path): string
    {
        if ($this->isFile($path) && $this->has($path)) {
            $content = self::box('\file_get_contents', $path);

            if (self::$lastError !== null) {
                throw new IOException(\sprintf('Reading failed for [%s]: %s.', $path, self::$lastError), 0, null, $path, self::$lastType);
            }

            return $content;
        }

        throw new NotFoundException(NotFoundException::TYPE_FILE, \sprintf('Reading failed for [%s], file could not be found.', $path));
    }

    /**
     * {@inheritdoc}
     */
    public function readStream(string $path): Generator
    {
        if (! $this->has($path)) {
            throw new NotFoundException(NotFoundException::TYPE_FILE, \sprintf('Reading failed for [%s], file could not be found.', $path));
        }

        $handle = self::box('\fopen', $path, 'rb');

        if (self::$lastError !== null) {
            throw new IOException(\sprintf('Opening stream for [%s] failed: %s.', $path, self::$lastError), 0, null, $path, self::$lastType);
        }

        while (! \feof($handle)) {
            $content = self::box('\fgets', $handle);

            if (self::$lastError !== null) {
                throw new IOException(\sprintf(' Getting line from file pointer failed: %s.', self::$lastError), 0, null, $path, self::$lastType);
            }

            yield \trim($content);
        }

        self::box('\fclose', $handle);

        if (self::$lastError !== null) {
            throw new IOException(\sprintf('Closing stream for [%s] failed: %s.', $path, self::$lastError), 0, null, $path, self::$lastType);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, string $content, array $config = []): void
    {
        $this->delete($path);

        $dir = \dirname($path);

        if (! \is_dir($dir)) {
            $this->createDirectory($dir);
        }

        if (! \is_writable($dir)) {
            throw new IOException(\sprintf('Unable to write to the [%s] directory.', $dir), 0, null, $dir);
        }

        self::box('\file_put_contents', Stream::PROTOCOL . '://' . $path, $content, isset($config['lock']) ? \LOCK_EX : 0);

        if (self::$lastError !== null) {
            throw new IOException(\sprintf('Could not write content to the file [%s]: %s', $path, self::$lastError), 0, null, $path, self::$lastType);
        }

        $this->changeFileVisibility($path, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream(string $path, $resource, array $config = []): void
    {
        $this->delete($path);

        $dir = \dirname($path);

        if (! \is_dir($dir)) {
            $this->createDirectory($dir);
        }

        if (! \is_writable($dir)) {
            throw new IOException(\sprintf('Unable to write to the [%s] directory.', $dir), 0, null, $dir);
        }

        $stream = self::box('\fopen', Stream::PROTOCOL . '://' . $path, 'w+b');

        if (self::$lastError !== null) {
            throw new IOException(self::$lastError, 0, null, $path, self::$lastType);
        }

        self::box('\stream_copy_to_stream', $resource, $stream);

        if (self::$lastError !== null) {
            throw new IOException(self::$lastError, 0, null, $path, self::$lastType);
        }

        self::box('\fclose', $stream);

        if (self::$lastError !== null) {
            throw new IOException(self::$lastError, 0, null, $path, self::$lastType);
        }

        $this->changeFileVisibility($path, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function append(string $path, string $content, array $config = []): void
    {
        if ($this->has($path)) {
            $config['flags'] = \FILE_APPEND;

            $this->update($path, $content, $config);
        } else {
            $this->write($path, $content, $config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function appendStream(string $path, $resource, array $config = []): void
    {
        if ($this->has($path)) {
            $config['flags'] = \FILE_APPEND;

            $this->updateStream($path, $resource, $config);
        } else {
            $this->writeStream($path, $resource, $config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $path, string $content, array $config = []): void
    {
        if (! $this->has($path)) {
            throw new NotFoundException(NotFoundException::TYPE_FILE, \sprintf('File [%s] could not be found.', $path));
        }

        self::box('\file_put_contents', $path, $content, $config['flags'] ?? (\array_key_exists('lock', $config) ? \LOCK_EX : 0));

        if (self::$lastError !== null) {
            throw new IOException(self::$lastError, 0, null, $path, self::$lastType);
        }

        $this->changeFileVisibility($path, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream(string $path, $resource, array $config = []): void
    {
        if (\ftell($resource) !== 0 && static::isSeekableStream($resource)) {
            \rewind($resource);
        }

        $content = self::box('\stream_get_contents', $resource);

        if (self::$lastError !== null) {
            throw new IOException(self::$lastError, 0, null, $path, self::$lastType);
        }

        $this->update($path, $content, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(string $file, $user): void
    {
        if (\function_exists('lchown') && \is_link($file)) {
            if (@\lchown($file, $user) !== true) {
                throw new IOException(\sprintf('Failed to chown file [%s].', $file), 0, null, $file);
            }
        } elseif (@\chown($file, $user) !== true) {
            throw new IOException(\sprintf('Failed to chown file [%s].', $file), 0, null, $file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup(string $file, $group): void
    {
        if (\function_exists('lchgrp') && \is_link($file)) {
            $result = self::box('\lchgrp', $file, $group);

            if ($result === false) {
                throw new IOException(\sprintf('Failed to lchgrp symlink file or directory [%s].', $file), 0, null, $file);
            }

            return;
        }

        $result = self::box('\chgrp', $file, $group);

        if ($result === false) {
            throw new IOException(\sprintf('Failed to chgrp file or directory [%s].', $file), 0, null, $file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility(string $path): int
    {
        \clearstatcache(false, $path);

        return (int) \substr(\sprintf('%o', \fileperms($path)), -3);
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility(string $path, $visibility, int $umask = 0000): void
    {
        if (! $this->has($path)) {
            throw new NotFoundException(NotFoundException::TYPE_ALL, \sprintf('File or Directory [%s] could not be found.', $path));
        }

        self::box('\chmod', $path, $this->parseVisibility($path, $visibility) & ~$umask);

        if (self::$lastError !== null) {
            throw new IOException(\sprintf('Failed to change chmod of the file [%s]: %s.', $path, self::$lastError), 0, null, $path, self::$lastType);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $originFile, string $targetFile, bool $override = false): void
    {
        $originIsLocal = \stream_is_local($originFile) || \stripos($originFile, 'file://') === 0;

        if ($originIsLocal && ! \is_file($originFile)) {
            throw new NotFoundException(NotFoundException::TYPE_FILE, \sprintf('Failed to copy [%s] because file does not exist.', $originFile), 0, null, $originFile);
        }

        if (! \is_dir($dir = \dirname($targetFile))) {
            $this->createDirectory($dir);
        }

        $doCopy = true;

        if (! $override && null === \parse_url($originFile, \PHP_URL_HOST) && \is_file($targetFile)) {
            $doCopy = \filemtime($originFile) > \filemtime($targetFile);
        }

        if ($doCopy) {
            /** @see https://bugs.php.net/64634 */
            $source = self::box('\fopen', $originFile, 'rb');

            if (self::$lastError !== null) {
                throw new IOException(\sprintf('Failed to copy [%s] to [%s] because source file could not be opened for reading: %s.', $originFile, $targetFile, self::$lastError), 0, null, $originFile, self::$lastType);
            }

            // Stream context created to allow files overwrite when using FTP stream wrapper - disabled by default
            if (false === $target = @\fopen($targetFile, 'wb', false, \stream_context_create(['ftp' => ['overwrite' => true]]))) {
                throw new IOException(\sprintf('Failed to copy [%s] to [%s] because target file could not be opened for writing.', $originFile, $targetFile), 0, null, $originFile);
            }

            $bytesCopied = \stream_copy_to_stream($source, $target);

            \fclose($source);
            \fclose($target);

            unset($source, $target);

            if (! \is_file($targetFile)) {
                throw new IOException(\sprintf('Failed to copy [%s] to [%s].', $originFile, $targetFile), 0, null, $originFile);
            }

            if ($originIsLocal) {
                // Like `cp`, preserve executable permission bits
                $visibility = self::box('fileperms', $targetFile) | (self::box('fileperms', $originFile) & 0111);

                if (self::$lastError !== null) {
                    throw new IOException(\sprintf('Failed to get file permissions: %s.', self::$lastError), 0, null, null, self::$lastType);
                }

                $this->setVisibility($targetFile, $visibility);

                if ($bytesCopied !== $bytesOrigin = \filesize($originFile)) {
                    throw new IOException(\sprintf('Failed to copy the whole content of [%s] to [%s] (%g of %g bytes copied).', $originFile, $targetFile, $bytesCopied, $bytesOrigin), 0, null, $originFile);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $from, string $to, array $config = []): void
    {
        $overwrite = $config['overwrite'] ?? false;

        // we check that target does not exist
        if (! $overwrite && $this->isReadable($to)) {
            throw new IOException(\sprintf('Cannot rename because the target [%s] already exists.', $to), 0, null, $to);
        }

        if (@\rename($from, $to) !== true) {
            if (\is_dir($from)) {
                // See https://bugs.php.net/54097 & https://php.net/rename#113943
                $this->mirror($from, $to, null, ['override' => $overwrite, 'delete' => $overwrite]);
                $this->deleteDirectory($from);

                return;
            }

            throw new IOException(\sprintf('Cannot rename [%s] to [%s].', $from, $to), 0, null, $to);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(string $path): int
    {
        if (! $this->has($path)) {
            throw new NotFoundException(NotFoundException::TYPE_FILE, null, 0, null, $path);
        }

        $fileSize = self::box('\filesize', $path);

        if (self::$lastError !== null) {
            throw new IOException(\sprintf('Failed to get file size of [%s]: %s.', $path, self::$lastError), 0, null, $path, self::$lastType);
        }

        return $fileSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype(string $path): string
    {
        if (! $this->isFile($path) && ! $this->has($path)) {
            throw new NotFoundException(NotFoundException::TYPE_FILE, $path);
        }

        $mimetype = MimeTypeFileExtensionGuesser::guess($path);

        if ($mimetype === null) {
            throw new IOException(\sprintf('No mime type was found for [%s].', $path), 0, null, $path);
        }

        return $mimetype;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModified(string $path): DateTime
    {
        if (! $this->isFile($path) && ! $this->has($path)) {
            throw new NotFoundException(NotFoundException::TYPE_FILE, $path);
        }

        $modifiedTimestamp = self::box('\filemtime', $path);

        if (self::$lastError !== null) {
            throw new IOException(self::$lastError, 0, null, $path, self::$lastType);
        }

        $modifiedDateTime = DateTime::createFromFormat('U', (string) $modifiedTimestamp);

        if ($modifiedDateTime === false) {
            throw new IOException('Failed to convert last modified time to DateTime object.');
        }

        return $modifiedDateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $file): void
    {
        if (\is_link($file)) {
            // See https://bugs.php.net/52176
            if ((self::box('\unlink', $file) === false || \PHP_OS_FAMILY !== 'Windows' || self::box('\rmdir', $file) === false) && \file_exists($file)) {
                throw new IOException(\sprintf('Failed to remove symlink [%s]: %s.', $file, self::$lastError), 0, null, $file, self::$lastType);
            }
        } elseif (self::box('\unlink', $file) === false && \file_exists($file)) {
            throw new IOException(\sprintf('Failed to remove file [%s]: %s.', $file, self::$lastError), 0, null, $file, self::$lastType);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function files(string $directory): Iterator
    {
        return $this->listContents($directory, 'isFile');
    }

    /**
     * {@inheritdoc}
     */
    public function allFiles(string $directory, bool $showHiddenFiles = false): Iterator
    {
        return $this->listContents($directory, 'isFile', true, $showHiddenFiles);
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(string $dirname, array $config = []): void
    {
        $visibility = $this->parseVisibility(
            $dirname,
            \array_key_exists('visibility', $config) ? $config['visibility'] : FilesystemContract::VISIBILITY_PUBLIC,
            'dir'
        );

        if (self::box('mkdir', $dirname, $visibility, true) === false) {
            if (! \is_dir($dirname)) {
                // The directory was not created by a concurrent process. Let's throw an exception with a developer friendly error message if we have one
                if (self::$lastError !== null) {
                    throw new IOException(\sprintf('Failed to create [%s]: %s.', $dirname, self::$lastError), 0, null, $dirname, self::$lastType);
                }

                throw new IOException(\sprintf('Failed to create [%s]', $dirname), 0, null, $dirname);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function directories(string $directory): Iterator
    {
        return $this->listContents($directory, 'isDir');
    }

    /**
     * {@inheritdoc}
     */
    public function allDirectories(string $directory): Iterator
    {
        return $this->listContents($directory, 'isDir', true);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory(string $dirname): void
    {
        $this->ensureDirectoryExist($dirname);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $path = $file->getPathname();

            // If the item is just a file, we can go ahead and delete it since we're
            // just looping through and waxing all of the files in this directory
            // and calling directories recursively, so we delete the real path.
            if (\is_link($path) || \is_file($path)) {
                $this->delete($path);
            } else {
                // If the item is a directory, we can just recurse into the function and
                // delete that sub-directory otherwise we'll just delete the file and
                // keep iterating through each file until the directory is cleaned.
                $this->deleteDirectory($path);
            }
        }

        if (self::box('\rmdir', $dirname) === false && \file_exists($dirname)) {
            throw new IOException(\sprintf('Failed to remove directory [%s]: %s.', $dirname, self::$lastError), 0, null, $dirname, self::$lastType);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanDirectory(string $dirname): void
    {
        $this->ensureDirectoryExist($dirname);

        /** @var SplFileInfo $file */
        foreach (new FilesystemIterator($dirname) as $file) {
            if ($file->isDir() && ! $file->isLink()) {
                $this->deleteDirectory($file->getPathname());
            } else {
                $this->delete($file->getPathname());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectory(string $dirname): bool
    {
        return \is_dir($dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function copyDirectory(string $directory, string $destination, array $config = []): void
    {
        $this->ensureDirectoryExist($directory);

        if (! $this->isDirectory($destination)) {
            $this->createDirectory($destination);
        }

        if (! \array_key_exists('flags', $config)) {
            $config['flags'] = FilesystemIterator::SKIP_DOTS;
        }

        $items = new FilesystemIterator($directory, $config['flags']);
        /** @var SplFileInfo $item */
        foreach ($items as $item) {
            $realPath = \realpath($item->getPathname());

            if ($realPath === false) {
                throw new NotFoundException(NotFoundException::TYPE_ALL, null, 0, null, $item->getPathname());
            }

            if ($item->isDir()) {
                $this->copyDirectory($realPath, $destination . \DIRECTORY_SEPARATOR . $item->getBasename(), $config);
            } elseif ($item->isFile()) {
                $this->copy($realPath, $destination . \DIRECTORY_SEPARATOR . $item->getBasename());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function watch($path, callable $callback, ?int $timeout = null): void
    {
        if (\extension_loaded('inotify')) {
            $watcher = new INotifyWatcher();
        } else {
            $watcher = new FileChangeWatcher();
        }

        $watcher->watch($path, $callback, $timeout);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension(string $path): string
    {
        return \pathinfo($path, \PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutExtension(string $path, ?string $extension = null): string
    {
        if ($extension !== null) {
            // remove extension and trailing dot
            return \rtrim(\basename($path, $extension), '.');
        }

        return \pathinfo($path, \PATHINFO_FILENAME);
    }

    /**
     * {@inheritdoc}
     */
    public function changeExtension(string $path, string $extension): string
    {
        $explode = \explode('.', $path);
        $substrPath = \substr($path, -1);

        // No extension for paths
        if ($substrPath === '/' || \is_dir($path)) {
            return $path;
        }

        $actualExtension = null;
        $extension = \ltrim($extension, '.');

        if (\count($explode) >= 2 && ! \is_dir($path)) {
            $actualExtension = \strtolower($extension);
        }

        // No actual extension in path
        if ($actualExtension === null) {
            return $path . ($substrPath === '.' ? '' : '.') . $extension;
        }

        return \substr($path, 0, -\strlen($actualExtension)) . $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(string $path): bool
    {
        return \is_writable($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isFile(string $file): bool
    {
        return \is_file($file);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(string $filename): bool
    {
        $maxPathLength = \PHP_MAXPATHLEN - 2;

        if (\strlen($filename) > $maxPathLength) {
            throw new IOException(\sprintf('Could not check if file is readable because path length exceeds %d characters.', $maxPathLength), 0, null, $filename);
        }

        return \is_readable($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function symlink(string $origin, string $target): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            $origin = \str_replace('/', '\\', $origin);
            $target = \str_replace('/', '\\', $target);
        }

        if (! \is_dir($target)) {
            $this->createDirectory(\dirname($target));
        }

        if ($this->isLink($target)) {
            if (\readlink($target) === $origin) {
                return;
            }

            if (\is_link($target) || \is_file($target)) {
                $this->delete($target);
            } else {
                $this->deleteDirectory($target);
            }
        }

        if (\PHP_OS_FAMILY !== 'Windows') {
            if (self::box('\symlink', $origin, $target) === false) {
                $this->linkException($origin, $target, 'symbolic');
            }

            return;
        }

        $output = $return = false;

        \exec("mklink /J /D \"{$target}\" \"{$origin}\"", $output, $return);
    }

    /**
     * {@inheritdoc}
     */
    public function hardlink(string $originFile, string $targetFile): void
    {
        if (! $this->has($originFile)) {
            throw new NotFoundException(NotFoundException::TYPE_FILE, null, 0, null, $originFile);
        }

        if (! $this->isFile($originFile)) {
            throw new NotFoundException(NotFoundException::TYPE_FILE, \sprintf('Origin file [%s] is not a file.', $originFile));
        }

        if (\is_file($targetFile)) {
            if (\fileinode($originFile) === \fileinode($targetFile)) {
                return;
            }

            $this->delete($targetFile);
        }

        if (self::box('link', $originFile, $targetFile) === false) {
            $this->linkException($originFile, $targetFile, 'hard');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readlink(string $path, bool $canonicalize = false): ?string
    {
        if (! $canonicalize && ! \is_link($path)) {
            return null;
        }

        if ($canonicalize) {
            if (! $this->has($path)) {
                return null;
            }

            if (\PHP_OS_FAMILY === 'Windows') {
                $path = \readlink($path);
            }

            $realpath = \realpath($path);

            return $realpath === false ? null : $realpath;
        }

        if (\PHP_OS_FAMILY === 'Windows') {
            $realpath = \realpath($path);

            return $realpath === false ? null : $realpath;
        }

        $readlink = self::box('\readlink', $path);

        if (self::$lastError !== null) {
            throw new IOException(\sprintf('Failed to return the target of a symbolic link: %s', self::$lastError), 0, null, $path, self::$lastType);
        }

        return $readlink;
    }

    /**
     * {@inheritdoc}
     */
    public function isLink(string $filename): bool
    {
        if (\PHP_OS_FAMILY !== 'Windows') {
            return \is_link($filename);
        }

        // handle windows stuff
        \clearstatcache();

        $fileType = self::box('\filetype', $filename);

        // symlink
        if ($fileType === 'link' && \is_link($filename) && $this->readlink($filename) !== null) {
            return true;
        }

        $lstat = @\lstat($filename);
        $stat = @\stat($filename);

        // junction
        if ($lstat !== false && $stat !== false && $fileType === 'unknown' && \file_exists($filename) && \count(\array_diff($stat, $lstat)) !== 0) {
            return true;
        }

        // file hardlink
        if ($stat !== false && $fileType === 'file' && $stat[7] /* size */ === 2) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function mirror(
        string $originDir,
        string $targetDir,
        ?Traversable $iterator = null,
        array $config = []
    ): void {
        $targetDir = \rtrim($targetDir, '/\\');
        $originDir = \rtrim($originDir, '/\\');
        $originDirLen = \strlen($originDir);

        if (! $this->has($originDir)) {
            throw new IOException(\sprintf('The origin directory specified [%s] was not found.', $originDir), 0, null, $originDir);
        }

        // Iterate in destination folder to remove obsolete entries
        if (\array_key_exists('delete', $config) && $config['delete'] && $this->has($targetDir)) {
            $deleteIterator = $iterator;

            if ($deleteIterator === null) {
                $flags = FilesystemIterator::SKIP_DOTS;
                $deleteIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetDir, $flags), RecursiveIteratorIterator::CHILD_FIRST);
            }

            $targetDirLen = \strlen($targetDir);

            /** @var \Viserio\Component\Finder\SplFileInfo $file */
            foreach ($deleteIterator as $file) {
                $origin = $originDir . \substr($file->getPathname(), $targetDirLen);

                if (! $this->has($origin)) {
                    if ($file->isFile() || $file->isLink()) {
                        $this->delete($file->getPathname());
                    } else {
                        $this->deleteDirectory($file->getPathname());
                    }
                }
            }
        }

        $followSymlinks = $config['follow_symlinks'] ?? false;

        if ($iterator === null) {
            $flags = $followSymlinks ? FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS : FilesystemIterator::SKIP_DOTS;
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($originDir, $flags), RecursiveIteratorIterator::SELF_FIRST);
        }

        $this->createDirectory($targetDir);

        $filesCreatedWhileMirroring = [];

        foreach ($iterator as $file) {
            if ($file->getPathname() === $targetDir || $file->getRealPath() === $targetDir || isset($filesCreatedWhileMirroring[$file->getRealPath()])) {
                continue;
            }

            $target = $targetDir . \substr($file->getPathname(), $originDirLen);
            $filesCreatedWhileMirroring[$target] = true;

            if (! ($config['copy_on_windows'] ?? false) && \is_link($file->getPathname())) {
                $this->symlink($file->getLinkTarget(), $target);
            } elseif (\is_dir($file->getPathname())) {
                $this->createDirectory($target);
            } elseif (\is_file($file->getPathname())) {
                $this->copy($file->getPathname(), $target, $config['override'] ?? false);
            } else {
                throw new IOException(\sprintf('Unable to guess [%s] file type.', $file->getPathname()), 0, null, $file->getPathname());
            }
        }
    }

    /**
     * @internal
     *
     * @param int    $type
     * @param string $msg
     *
     * @return bool;
     */
    public static function handleError(int $type, string $msg): bool
    {
        self::$lastError = $msg;
        self::$lastType = $type;

        return true;
    }

    /**
     * @param string $origin
     * @param string $target
     * @param string $linkType Name of the link type, typically 'symbolic' or 'hard'
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     *
     * @return void
     */
    private function linkException(string $origin, string $target, string $linkType): void
    {
        if (self::$lastError !== null) {
            if (\PHP_OS_FAMILY === 'Windows' && \strpos(self::$lastError, 'error code(1314)') !== false) {
                throw new IOException(\sprintf('Unable to create %s link due to error code 1314: \'A required privilege is not held by the client\'. Do you have the required Administrator-rights?', $linkType), 0, null, $target);
            }
        }

        throw new IOException(\sprintf('Failed to create %s link from [%s] to [%s].', $linkType, $origin, $target), 0, null, $target);
    }

    /**
     * Parse the given visibility value.
     *
     * @param string           $path
     * @param float|int|string $visibility
     * @param null|string      $type
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return float|int|string
     */
    private function parseVisibility(string $path, $visibility, ?string $type = null)
    {
        if ((\is_string($visibility) && $visibility === '') || (! \is_numeric($visibility) && ! \in_array($visibility, [FilesystemContract::VISIBILITY_PUBLIC, FilesystemContract::VISIBILITY_PRIVATE], true))) {
            throw new NotSupportedException('Given value for the file or directory visibility, is not supported. Use [int],[float] or [string].');
        }

        if ($type === null && \is_file($path)) {
            $type = 'file';
        } elseif ($type === null && \is_dir($path)) {
            $type = 'dir';
        }

        if ($type !== null && $visibility === FilesystemContract::VISIBILITY_PUBLIC) {
            return self::$permissions[$type]['public'];
        }

        if ($type !== null && $visibility === FilesystemContract::VISIBILITY_PRIVATE) {
            return self::$permissions[$type]['private'];
        }

        return Permissions::notation($visibility);
    }

    /**
     * @codeCoverageIgnore
     *
     * Call the given callable with given args, but throws an ErrorException when an error/warning/notice is triggered.
     *
     * @param callable $func
     *
     * @throws Throwable
     *
     * @return mixed
     */
    private static function box(callable $func)
    {
        self::$lastError = null;
        self::$lastType = null;

        \set_error_handler(__CLASS__ . '::handleError', \E_ALL & ~\E_DEPRECATED & ~\E_USER_DEPRECATED);

        try {
            $result = $func(...\array_slice(\func_get_args(), 1));

            \restore_error_handler();

            return $result;
        } catch (Throwable $e) {
            // @ignoreException
        }

        \restore_error_handler();

        throw $e;
    }

    /**
     * Check if the given dir path exists.
     *
     * @param string $dirname
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     *
     * @return void
     */
    private function ensureDirectoryExist(string $dirname): void
    {
        if (! $this->isDirectory($dirname)) {
            throw new NotFoundException(NotFoundException::TYPE_DIR, null, 0, null, $dirname);
        }
    }

    /**
     * Changing visibility on a given file path.
     *
     * @param string                    $path
     * @param array<string, int|string> $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     *
     * @return void
     */
    private function changeFileVisibility(string $path, array $config): void
    {
        /** @var int $unmask */
        $unmask = \array_key_exists('unmask', $config) ? $config['unmask'] : 0000;

        if (\array_key_exists('visibility', $config)) {
            $this->setVisibility($path, $this->parseVisibility($path, $config['visibility'], 'file'), $unmask);
        } else {
            $this->setVisibility($path, self::$permissions['file'][FilesystemContract::VISIBILITY_PUBLIC], $unmask);
        }
    }

    /**
     * Gets all of the files or directories at the input path.
     *
     * @param string $directory
     * @param string $method
     * @param bool   $isRecursive    Whether or not we should recurse through child directories
     * @param bool   $shoHiddenFiles
     *
     * @throws \Viserio\Contract\Finder\Exception\NotFoundException
     *
     * @return Iterator<string, \SplFileInfo>
     */
    private function listContents(
        string $directory,
        string $method,
        bool $isRecursive = false,
        bool $shoHiddenFiles = false
    ): Iterator {
        if (! $this->isDirectory($directory)) {
            return new EmptyIterator();
        }

        $flags = RecursiveDirectoryIterator::SKIP_DOTS;

        if ($method === 'isFile') {
            $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO;

            if (! $shoHiddenFiles) {
                $flags |= FilesystemIterator::SKIP_DOTS;
            }
        }

        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, $flags),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        if (! $isRecursive) {
            $iter->setMaxDepth(0);
        }

        return new SplFileInfoMethodFilterIterator($iter, $method);
    }

    /**
     * Check if stream is seekable.
     *
     * @param resource $resource
     *
     * @return bool
     */
    private static function isSeekableStream($resource): bool
    {
        $metadata = \stream_get_meta_data($resource);

        return $metadata['seekable'];
    }
}
