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

namespace Viserio\Contract\View\Exception;

use Throwable;

class IOException extends RuntimeException
{
    /** @var null|string */
    private $path;

    /**
     * Create a new IO exception.
     */
    public function __construct(string $message, $code = 0, ?Throwable $previous = null, ?string $path = null)
    {
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the file path.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
