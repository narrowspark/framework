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

namespace Viserio\Contract\Filesystem\Exception;

use Throwable;

class IOException extends RuntimeException
{
    /** @var null|string */
    private $path;

    /**
     * Create a new IO exception.
     *
     * @param string          $message
     * @param mixed           $code
     * @param null|\Throwable $previous
     * @param null|string     $path
     */
    public function __construct(string $message, $code = 0, Throwable $previous = null, string $path = null)
    {
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the file path.
     *
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
