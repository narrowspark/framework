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

namespace Viserio\Contract\Filesystem;

use DateTime;
use Generator;
use Iterator;
use SplFileInfo;
use Throwable;
use Traversable;

interface Filesystem extends DirectorySystem
{
    /**
     * The public visibility setting.
     *
     * @var string
     */
    public const VISIBILITY_PUBLIC = 'public';

    /**
     * The private visibility setting.
     *
     * @var string
     */
    public const VISIBILITY_PRIVATE = 'private';

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     *
     * @return bool
     */
    public function has(string $path): bool;

    /**
     * Read a file.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     *
     * @return false|string the file content or false on failure
     */
    public function read(string $path);

    /**
     * Retrieves a generator with file content for the given path.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     *
     * @return Generator<string>
     */
    public function readStream(string $path): Generator;

    /**
     * Write a new file.
     *
     * @param string                    $path    the path of the new file
     * @param string                    $content the file content
     * @param array<string, int|string> $config  An array of boolean options
     *                                           Valid options are:
     *                                           - $config['lock'] Acquire an exclusive lock on the file while proceeding to the writing
     *                                           - $config['visibility'] Whether to change the file chmod (defaults to public visibility)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     *
     * @return void
     */
    public function write(string $path, string $content, array $config = []): void;

    /**
     * Write a new file using a stream.
     *
     * @param string                    $path
     * @param resource                  $resource
     * @param array<string, int|string> $config   An array of boolean options
     *                                            Valid options are:
     *                                            - $config['visibility'] Whether to change the file chmod (defaults to public visibility)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     *
     * @return void
     */
    public function writeStream(string $path, $resource, array $config = []): void;

    /**
     * Appends the given content to the specified file.
     * If the file does not exist, the file will be created.
     *
     * @param string                    $path
     * @param string                    $content
     * @param array<string, int|string> $config  An array of boolean options
     *                                           Valid options are:
     *                                           - $config['visibility'] Whether to change the file chmod (defaults to public visibility)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     *
     * @return void
     */
    public function append(string $path, string $content, array $config = []): void;

    /**
     * Appends the given content to the specified file using stream.
     * If the file does not exist, the file will be created.
     *
     * @param string                    $path
     * @param resource                  $resource
     * @param array<string, int|string> $config   An array of boolean options
     *                                            Valid options are:
     *                                            - $config['visibility'] Whether to change the file chmod (defaults to public visibility)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     *
     * @return void
     */
    public function appendStream(string $path, $resource, array $config = []): void;

    /**
     * Update an existing file with the given content.
     *
     * @param string                    $path    the path of the existing file
     * @param string                    $content the file content
     * @param array<string, int|string> $config  An array of boolean options
     *                                           Valid options are:
     *                                           - $config['flags'] @see https://php.net/manual/en/function.file-put-contents.php and than the flags section
     *                                           - $config['visibility'] Whether to change the file chmod (defaults to public visibility)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     *
     * @return void
     */
    public function update(string $path, string $content, array $config = []): void;

    /**
     * Update a file using a stream.
     *
     * @param string                    $path
     * @param resource                  $resource
     * @param array<string, int|string> $config   An array of boolean options
     *                                            Valid options are:
     *                                            - $config['flags'] @see https://php.net/manual/en/function.file-put-contents.php and the flags section
     *                                            - $config['visibility'] Whether to change the file chmod (defaults to public visibility)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException if the getting of the stream content failed
     *
     * @return void
     */
    public function updateStream(string $path, $resource, array $config = []): void;

    /**
     * Change the owner of a file or directory.
     *
     * @param string     $file
     * @param int|string $user A user name or number
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException When the change fails
     *
     * @return void
     */
    public function setOwner(string $file, $user): void;

    /**
     * Change the group of an array of files or directories.
     *
     * @param string     $file
     * @param int|string $group A group name or number
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException When the change fails
     *
     * @return void
     */
    public function setGroup(string $file, $group): void;

    /**
     * Get the visibility for the given path.
     *
     * @param string $path
     *
     * @return int
     */
    public function getVisibility(string $path): int;

    /**
     * Set the visibility for the given path.
     *
     * @param string           $path
     * @param float|int|string $visibility The new mode (octal)
     * @param int              $umask      The mode mask (octal)
     *
     * @return void
     */
    public function setVisibility(string $path, $visibility, int $umask = 0000): void;

    /**
     * Copies a file.
     *
     * This method only copies the file if the origin file is newer than the target file.
     *
     * By default, if the target already exists, it is not overridden.
     *
     * @param string $originFile The original filename
     * @param string $targetFile The target filename
     * @param bool   $override   Whether to override an existing file or not
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException When originFile doesn't exist
     * @throws \Viserio\Contract\Filesystem\Exception\IOException       When copy fails
     *
     * @return void
     */
    public function copy(string $originFile, string $targetFile, bool $override = false): void;

    /**
     * Move a file to a new location.
     *
     * @param string              $from
     * @param string              $to
     * @param array<string, bool> $config An array of boolean options
     *                                    Valid options are:
     *                                    - $config['overwrite'] If true, target files newer than origin files are overwritten (see copy(), defaults to false)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\UnreadableFileException
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     *
     * @return void
     */
    public function move(string $from, string $to, array $config = []): void;

    /**
     * Get a file's size.
     *
     * @param string $path the path to the file
     *
     * @return int
     */
    public function getSize(string $path): int;

    /**
     * Get a file's mime-type.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     *
     * @return string the file mime-type
     */
    public function getMimetype(string $path): string;

    /**
     * Gets the last modified time of the file.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\IOException       If a non-numeric value is used for timestamp
     *
     * @return DateTime The last modified time
     */
    public function getLastModified(string $path): DateTime;

    /**
     * Delete the file at a given path.
     *
     * @param string $path
     *
     * @return void
     */
    public function delete(string $path): void;

    /**
     * Get an array of all files in a directory.
     *
     * @param string $directory
     *
     * @return Iterator<string, SplFileInfo>
     */
    public function files(string $directory): Iterator;

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param string $directory
     * @param bool   $showHiddenFiles
     *
     * @return Iterator<string, SplFileInfo>
     */
    public function allFiles(string $directory, bool $showHiddenFiles = false): Iterator;

    /**
     * Extract the file extension from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getExtension(string $path): string;

    /**
     * Returns the filename without the extension from a file path.
     *
     * @param string      $path      The path string
     * @param null|string $extension If specified, only that extension is cut off
     *                               (may contain leading dot)
     *
     * @return string Filename without extension
     */
    public function withoutExtension(string $path, ?string $extension = null): string;

    /**
     * Changes the extension of a path string.
     *
     * @param string $path      The path string with filename.ext to change
     * @param string $extension New extension (with or without leading dot)
     *
     * @return string The path string with new file extension
     */
    public function changeExtension(string $path, string $extension): string;

    /**
     * Check if path is writable.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isWritable(string $path): bool;

    /**
     * Tells whether a file exists and is readable.
     *
     * @param string $filename
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException When windows path is longer than 258 characters
     *
     * @return bool
     */
    public function isReadable(string $filename): bool;

    /**
     * Check if path is a file.
     *
     * @param string $file
     *
     * @return bool
     */
    public function isFile(string $file): bool;

    /**
     * Mirrors a directory to another.
     *
     * Copies files and directories from the origin directory into the target directory. By default:
     *
     *  - existing files in the target directory will be overwritten, except if they are newer (see the `override` option)
     *  - files in the target directory that do not exist in the source directory will not be deleted (see the `delete` option)
     *
     * @param string                  $originDir
     * @param string                  $targetDir
     * @param null|Traversable<mixed> $iterator  Iterator that filters which files and directories to copy, if null a recursive iterator is created
     * @param array<string, bool>     $config    An array of boolean options
     *                                           Valid options are:
     *                                           - $config['override'] If true, target files newer than origin files are overwritten (see copy(), defaults to false)
     *                                           - $config['copy_on_windows'] Whether to copy files instead of links on Windows (see symlink(), defaults to false)
     *                                           - $config['follow_symlinks'] Whether to follow symlinks
     *                                           - $config['delete'] Whether to delete files that are not in the source directory (defaults to false)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException   When file type is unknown
     * @throws \Viserio\Contract\Finder\Exception\NotFoundException
     * @throws Throwable
     *
     * @return void
     */
    public function mirror(
        string $originDir,
        string $targetDir,
        ?Traversable $iterator = null,
        array $config = []
    ): void;
}
