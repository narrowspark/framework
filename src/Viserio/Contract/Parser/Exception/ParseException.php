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

namespace Viserio\Contract\Parser\Exception;

use Throwable;

class ParseException extends RuntimeException
{
    /**
     * Create a new ParseException instance.
     */
    public function __construct(
        string $message,
        int $code = 0,
        string $file = __FILE__,
        int $line = __LINE__,
        ?Throwable $exception = null
    ) {
        $this->file = $file;
        $this->line = $line;

        parent::__construct($message, $code, $exception);
    }

    /**
     * Helper to create exception from a catch exception.
     *
     * @return static
     */
    public static function createFromException(string $message, Throwable $exception): self
    {
        return new static($message, $exception->getCode(), $exception->getFile(), $exception->getLine(), $exception);
    }
}
