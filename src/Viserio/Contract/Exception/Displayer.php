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

namespace Viserio\Contract\Exception;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface Displayer
{
    /**
     * Display the given exception.
     *
     * @param string[] $headers
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface;

    /**
     * Get the supported content type.
     */
    public function getContentType(): string;

    /**
     * Can we display the exception?
     */
    public function canDisplay(Throwable $original, Throwable $transformed, int $code): bool;

    /**
     * Do we provide verbose information about the exception?
     */
    public function isVerbose(): bool;
}
