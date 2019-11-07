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

namespace Viserio\Contract\Exception;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface Displayer
{
    /**
     * Display the given exception.
     *
     * @param Throwable $exception
     * @param string    $id
     * @param int       $code
     * @param string[]  $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface;

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * Can we display the exception?
     *
     * @param Throwable $original
     * @param Throwable $transformed
     * @param int       $code
     *
     * @return bool
     */
    public function canDisplay(Throwable $original, Throwable $transformed, int $code): bool;

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose(): bool;
}
