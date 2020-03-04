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

use ErrorException as BaseErrorException;
use Throwable;

class IOException extends BaseErrorException implements Exception
{
    /** @var null|string */
    private $path;

    /**
     * Create a new IOException instance.
     */
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
        ?string $path = null,
        ?int $severity = null,
        string $filename = __FILE__,
        int $lineno = __LINE__
    ) {
        $this->path = $path;

        parent::__construct($message, $code, $severity ?? 1, $filename, $lineno, $previous);
    }

    /**
     * Get the file/dir path.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
