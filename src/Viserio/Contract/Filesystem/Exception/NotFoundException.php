<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Filesystem\Exception;

use Exception as BaseException;
use Throwable;

class NotFoundException extends BaseException implements Exception
{
    /** @var string */
    public const TYPE_DIR = 'dir';

    /** @var string */
    public const TYPE_FILE = 'file';

    /** @var string */
    public const TYPE_ALL = self::TYPE_FILE . '_' . self::TYPE_DIR;

    /**
     * Path to the file or directory.
     *
     * @var null|string
     */
    private $path;

    /** @var null|string */
    private $type;

    /**
     * Create a new DirectoryNotFoundException instance.
     */
    public function __construct(
        string $type,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        ?string $path = null
    ) {
        $this->type = $type;
        $this->path = $path;

        if ($message === null) {
            if ($path === null) {
                $message = \sprintf('%s could not be found.', $type === 'file' ? 'File' : 'Directory');
            } else {
                $message = \sprintf('%s [%s] could not be found.', $type === 'file' ? 'File' : 'Directory', $path);
            }
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the file or directory path.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Check if exception is a file exception.
     */
    public function isFile(): bool
    {
        return $this->type === 'file' || $this->type === self::TYPE_ALL;
    }

    /**
     * Check if exception is a dir exception.
     */
    public function isDir(): bool
    {
        return $this->type === 'dir' || $this->type === self::TYPE_ALL;
    }
}
