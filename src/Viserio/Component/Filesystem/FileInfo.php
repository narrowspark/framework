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

use SplFileInfo;
use Viserio\Contract\Filesystem\Exception\NotFoundException;
use Viserio\Contract\Filesystem\Exception\RuntimeException;

final class FileInfo extends SplFileInfo
{
    /** @var string */
    private $relativePath;

    /** @var string */
    private $relativePathname;

    /**
     * Create a new FileInfo instance.
     *
     * @param string $filePath
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     */
    public function __construct(string $filePath)
    {
        $realPath = \realpath($filePath);

        if ($realPath === false || ! file_exists($filePath)) {
            throw new NotFoundException(NotFoundException::TYPE_FILE, null, 0, null, $filePath);
        }

        /** @var string $cwd */
        $cwd = \getcwd();

        $relativeFilePath = \rtrim(Path::makeRelative($realPath, $cwd), '/');

        parent::__construct($filePath);

        $this->relativePath = dirname($relativeFilePath);
        $this->relativePathname = $relativeFilePath;
    }

    /**
     * Returns the relative path.
     *
     * This path does not contain the file name.
     *
     * @return string the relative path
     */
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    /**
     * Returns the relative path name.
     *
     * This path contains the file name.
     *
     * @return string the relative path name
     */
    public function getRelativePathname(): string
    {
        return $this->relativePathname;
    }

    /**
     * Returns the basename without a suffix.
     *
     * @return string
     */
    public function getBasenameWithoutSuffix(): string
    {
        return \pathinfo($this->getFilename())['filename'];
    }

    /**
     * @param string $directory
     *
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     *
     * @return string
     */
    public function getRelativeFilePathFromDirectory(string $directory): string
    {
        if (! file_exists($directory)) {
            throw new NotFoundException(NotFoundException::TYPE_DIR, \sprintf('Directory [%s] was not found in [%s].', $directory, self::class));
        }

        return \rtrim(
            Path::makeRelative($this->getNormalizedRealPath(), (string) \realpath($directory)),
            '/'
        );
    }

    /**
     * Check if the file path ends with the given string.
     *
     * @param string $string
     *
     * @return bool
     */
    public function endsWith(string $string): bool
    {
        return \mb_strpos($this->getNormalizedRealPath(), $string) !== false;
    }

    /**
     * Return the given path without a extension.
     *
     * @return string
     */
    public function getFilenameWithoutExtension(): string
    {
        return \pathinfo($this->getFilename(), \PATHINFO_FILENAME);
    }

    /**
     * Returns the contents of the file.
     *
     * @throws \Viserio\Contract\Filesystem\Exception\RuntimeException
     *
     * @return string the contents of the file
     */
    public function getContents(): string
    {
        \set_error_handler(static function (int $errno, string $errstr) use (&$error): bool {
            $error = $errstr;

            return true;
        });

        /** @var string $content */
        $content = \file_get_contents($this->getPathname());

        \restore_error_handler();

        if (\is_string($error)) {
            throw new RuntimeException($error);
        }

        return $content;
    }

    /**
     * Normalize the real path.
     *
     * @return string
     */
    public function getNormalizedRealPath(): string
    {
        /** @var string $realpath */
        $realpath = $this->getRealPath();

        return \str_replace('\\', '/', $realpath);
    }
}
