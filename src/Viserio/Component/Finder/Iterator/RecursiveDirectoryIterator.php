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

namespace Viserio\Component\Finder\Iterator;

use FilesystemIterator;
use RecursiveArrayIterator;
use RecursiveDirectoryIterator as BaseRecursiveDirectoryIterator;
use RecursiveIterator;
use UnexpectedValueException;
use Viserio\Component\Finder\SplFileInfo;
use Viserio\Contract\Finder\Exception\AccessDeniedException;
use Viserio\Contract\Finder\Exception\RuntimeException;

/**
 * Recursive directory iterator that is working during recursive iteration.
 */
final class RecursiveDirectoryIterator extends BaseRecursiveDirectoryIterator
{
    /** @var bool */
    private $normalizeKey;

    /** @var bool */
    private $ignoreUnreadableDirs;

    /** @var bool */
    private $rewindable;

    // these 3 properties take part of the performance optimization to avoid redoing the same work in all iterations

    /** @var string */
    private $rootPath;

    /** @var null|string */
    private $subPath;

    /** @var string */
    private $directorySeparator = '/';

    /**
     * Create a new RecursiveDirectoryIterator instance.
     *
     * @param string $path
     * @param int    $flags
     * @param bool   $ignoreUnreadableDirs
     *
     * @throws \Viserio\Contract\Finder\Exception\RuntimeException
     * @throws \Viserio\Contract\Finder\Exception\UnexpectedValueException
     */
    public function __construct(
        string $path,
        int $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO,
        bool $ignoreUnreadableDirs = false
    ) {
        if (($flags & (FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::CURRENT_AS_SELF)) !== 0) {
            throw new RuntimeException('This iterator only support returning current as fileinfo.');
        }

        parent::__construct($path, $flags);

        $this->ignoreUnreadableDirs = $ignoreUnreadableDirs;
        $this->rootPath = $this->getPath();

        if (\DIRECTORY_SEPARATOR !== '/' && ($flags & self::UNIX_PATHS) === 0) {
            $this->directorySeparator = \DIRECTORY_SEPARATOR;
        }

        // Normalize slashes on Windows
        $this->normalizeKey = \PHP_OS_FAMILY === 'Windows' && ($flags & self::KEY_AS_FILENAME) === 0;
    }

    /**
     * Checks if the stream is rewindable.
     *
     * @return bool true when the stream is rewindable, false otherwise
     */
    public function isRewindable(): bool
    {
        if ($this->rewindable !== null) {
            return $this->rewindable;
        }

        if (false !== $stream = @\opendir($this->getPath())) {
            $infos = \stream_get_meta_data($stream);

            \closedir($stream);

            if ($infos['seekable']) {
                return $this->rewindable = true;
            }
        }

        return $this->rewindable = false;
    }

    /**
     * Returns an iterator for the current entry if it is a directory.
     *
     * @throws \Viserio\Contract\Finder\Exception\AccessDeniedException
     *
     * @return RecursiveIterator<int|string, \SplFileInfo>
     */
    public function getChildren(): RecursiveIterator
    {
        try {
            /** @var \RecursiveIterator<int|string, \SplFileInfo> $children */
            $children = parent::getChildren();

            if ($children instanceof self) {
                // parent method will call the constructor with default arguments, so unreadable dirs won't be ignored anymore
                $children->ignoreUnreadableDirs = $this->ignoreUnreadableDirs;

                // performance optimization to avoid redoing the same work in all children
                $children->rewindable = &$this->rewindable;
                $children->rootPath = $this->rootPath;
            }

            return $children;
        } catch (UnexpectedValueException $e) {
            if ($this->ignoreUnreadableDirs) {
                // If directory is unreadable and finder is set to ignore it, a fake empty content is returned.
                return new RecursiveArrayIterator([]);
            }

            throw new AccessDeniedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key(): string
    {
        $key = parent::key();

        if ($this->normalizeKey) {
            $key = \str_replace('\\', '/', $key);
        }

        return $key;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Contract\Finder\Exception\NotFoundException
     */
    public function current(): SplFileInfo
    {
        // the logic here avoids redoing the same work in all iterations
        if (null === $subPathname = $this->subPath) {
            $subPathname = $this->subPath = $this->getSubPath();
        }

        if ($subPathname !== '') {
            $subPathname .= $this->directorySeparator;
        }

        $subPathname .= $this->getFilename();

        return new SplFileInfo($this->rootPath . $this->directorySeparator . $subPathname, (string) $this->subPath, $subPathname);
    }

    /**
     * Do nothing for non rewindable stream.
     *
     * @return void
     */
    public function rewind(): void
    {
        if ($this->isRewindable() === false) {
            return;
        }

        parent::rewind();
    }
}
