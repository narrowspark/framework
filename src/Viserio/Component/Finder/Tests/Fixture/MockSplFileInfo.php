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

namespace Viserio\Component\Finder\Tests\Fixture;

use RuntimeException;
use SplFileInfo;

class MockSplFileInfo extends SplFileInfo
{
    private const TYPE_DIRECTORY = 1;
    private const TYPE_FILE = 2;
    private const TYPE_UNKNOWN = 3;

    private $contents;

    private $mode;

    private $type;

    private $relativePath;

    private $relativePathname;

    private $subPath;

    private $subPathname;

    /**
     * @param array|string $param
     */
    public function __construct($param)
    {
        if (\is_string($param)) {
            parent::__construct($param);
        } elseif (\is_array($param)) {
            $defaults = [
                'name' => 'file.txt',
                'contents' => null,
                'mode' => null,
                'type' => null,
                'relativePath' => null,
                'relativePathname' => null,
                'subPath' => null,
                'subPathname' => null,
            ];

            $defaults = \array_merge($defaults, $param);

            parent::__construct($defaults['name']);

            $this->setContents($defaults['contents']);
            $this->setMode($defaults['mode']);
            $this->setType($defaults['type']);
            $this->setRelativePath($defaults['relativePath']);
            $this->setRelativePathname($defaults['relativePathname']);
            $this->setSubPath($defaults['subPath']);
            $this->setSubPathname($defaults['subPathname']);
        } else {
            throw new RuntimeException(\sprintf('Incorrect parameter [%s]', $param));
        }
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function setContents($contents): void
    {
        $this->contents = $contents;
    }

    public function setMode($mode): void
    {
        $this->mode = $mode;
    }

    public function setType($type): void
    {
        if (\is_string($type)) {
            switch ($type) {
                case 'directory':
                case 'd':
                    $this->type = self::TYPE_DIRECTORY;

                    break;
                case 'file':
                case 'f':
                    $this->type = self::TYPE_FILE;

                    break;

                default:
                    $this->type = self::TYPE_UNKNOWN;
            }
        } else {
            $this->type = $type;
        }
    }

    public function getRelativePath()
    {
        return $this->relativePath;
    }

    public function setRelativePath($relativePath): void
    {
        $this->relativePath = $relativePath;
    }

    public function getRelativePathname()
    {
        return $this->relativePathname;
    }

    public function setRelativePathname($relativePathname): void
    {
        $this->relativePathname = $relativePathname;
    }

    /**
     * Get sub path.
     *
     * @return string The sub path (sub directory)
     */
    public function getSubPath(): string
    {
        return $this->subPath;
    }

    /**
     * @param mixed $subPath
     */
    public function setSubPath($subPath): void
    {
        $this->subPath = $subPath;
    }

    /**
     * Returns the relative sub path name.
     *
     * @return string the relative path name
     */
    public function getSubPathname(): string
    {
        return $this->subPathname;
    }

    /**
     * @param mixed $subPathname
     */
    public function setSubPathname($subPathname): void
    {
        $this->subPathname = $subPathname;
    }

    public function isFile(): bool
    {
        if (null === $this->type) {
            return false !== \strpos($this->getFilename(), 'file');
        }

        return self::TYPE_FILE === $this->type;
    }

    public function isDir(): bool
    {
        if (null === $this->type) {
            return false !== \strpos($this->getFilename(), 'directory');
        }

        return self::TYPE_DIRECTORY === $this->type;
    }

    public function isReadable()
    {
        if (null === $this->mode) {
            return \preg_match('/r\+/', $this->getFilename());
        }

        return \preg_match('/r\+/', $this->mode);
    }
}
