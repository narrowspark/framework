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

namespace Viserio\Component\Finder;

use SplFileInfo as BaseSplFileInfo;
use Viserio\Component\Filesystem\Path;
use Viserio\Contract\Finder\Exception\NotFoundException;
use Viserio\Contract\Finder\Exception\RuntimeException;
use Viserio\Contract\Finder\SplFileInfo as SplFileInfoContract;

final class SplFileInfo extends BaseSplFileInfo implements SplFileInfoContract
{
    /** @var string */
    private $relativePath;

    /** @var string */
    private $relativePathname;

    /** @var string */
    private $subPath;

    /** @var string */
    private $subPathname;

    /**
     * Create a new SplFileInfo instance.
     *
     * @param string $filePath
     * @param string $subPath
     * @param string $subPathname
     *
     * @throws \Viserio\Contract\Finder\Exception\NotFoundException
     */
    public function __construct(string $filePath, string $subPath, string $subPathname)
    {
        if (false !== $streamUrlPos = \strpos($filePath, '://')) {
            $realPath = $filePath;
            $basePath = \substr($filePath, 0, $streamUrlPos + 4); // the first backslash needs to be removed too
        } else {
            $realPath = \realpath($filePath);

            if ($realPath === false) {
                throw new NotFoundException(NotFoundException::TYPE_FILE, null, 0, null, $filePath);
            }

            \error_clear_last();

            $basePath = \getcwd();

            if ($basePath === false) {
                $error = \error_get_last();

                throw new RuntimeException($error['message'] ?? 'An error occured', $error['type'] ?? 1);
            }
        }

        parent::__construct($filePath);

        $relativeFilePath = \rtrim(Path::makeRelative($realPath, $basePath), '/');

        $this->relativePath = dirname($relativeFilePath);
        $this->relativePathname = $relativeFilePath;
        $this->subPath = $subPath;
        $this->subPathname = $subPathname;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativePathname(): string
    {
        return $this->relativePathname;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubPath(): string
    {
        return $this->subPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubPathname(): string
    {
        return $this->subPathname;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativeFilePathFromDirectory(string $directory): string
    {
        $realPath = \realpath($directory);

        if ($realPath === false) {
            throw new NotFoundException(NotFoundException::TYPE_DIR, null, 0, null, $directory);
        }

        return \rtrim(
            Path::makeRelative($this->getNormalizedRealPath(), $realPath),
            '/'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function endsWith(string $string): bool
    {
        return \mb_strpos($this->getNormalizedRealPath(), $string) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilenameWithoutExtension(): string
    {
        return \pathinfo($this->getFilename(), \PATHINFO_FILENAME);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getNormalizedPathname(): string
    {
        $pathname = $this->getPathname();

        return \str_replace('\\', '/', $pathname);
    }

    /**
     * {@inheritdoc}
     */
    public function getNormalizedRealPath(): string
    {
        /** @var string $realpath */
        $realpath = $this->getRealPath();

        return \str_replace('\\', '/', $realpath);
    }
}
