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

use ErrorException as BaseErrorException;
use Throwable;

class IOException extends BaseErrorException implements Exception
{
    /** @var null|string */
    private $path;

    /**
     * Create a new IOException instance.
     *
     * @param string         $message
     * @param int            $code
     * @param null|Throwable $previous
     * @param null|string    $path
     * @param null|int       $severity
     * @param string         $filename
     * @param int            $lineno
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
     *
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
