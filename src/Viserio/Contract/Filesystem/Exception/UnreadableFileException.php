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

use Exception as BaseException;
use Throwable;

class UnreadableFileException extends BaseException implements Exception
{
    /** @var null|string */
    private $path;

    /**
     * Create a new FileNotFoundException instance.
     *
     * @param string         $path
     * @param int            $code
     * @param null|Throwable $previous
     */
    public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(\sprintf('Unreadable file encountered: [%s].', $path), $code, $previous);

        $this->path = $path;
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
