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

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface Filter
{
    /**
     * Filter and return the displayers.
     *
     * @param \Viserio\Contract\Exception\Displayer[] $displayers
     *
     * @return \Viserio\Contract\Exception\Displayer[]
     */
    public function filter(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array;
}
