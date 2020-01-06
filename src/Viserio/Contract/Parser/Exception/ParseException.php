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

namespace Viserio\Contract\Parser\Exception;

use Throwable;

class ParseException extends RuntimeException
{
    /**
     * Create a new ParseException instance.
     *
     * @param string         $message
     * @param int            $code
     * @param string         $file
     * @param int            $line
     * @param null|Throwable $exception
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
     * @param string     $message
     * @param \Throwable $exception
     *
     * @return static
     */
    public static function createFromException(string $message, Throwable $exception): self
    {
        return new static($message, $exception->getCode(), $exception->getFile(), $exception->getLine(), $exception);
    }
}
