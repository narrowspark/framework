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

class UnreadableFileException extends BaseException implements Exception
{
    /** @var null|string */
    private $path;

    /**
     * Create a new FileNotFoundException instance.
     */
    public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(\sprintf('Unreadable file encountered: [%s].', $path), $code, $previous);

        $this->path = $path;
    }

    /**
     * Get the file path.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
