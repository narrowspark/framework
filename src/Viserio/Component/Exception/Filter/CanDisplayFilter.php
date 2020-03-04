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

namespace Viserio\Component\Exception\Filter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Contract\Exception\Filter as FilterContract;

class CanDisplayFilter implements FilterContract
{
    /**
     * {@inheritdoc}
     */
    public function filter(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        foreach ($displayers as $index => $displayer) {
            if (! $displayer->canDisplay($original, $transformed, $code)) {
                unset($displayers[$index]);
            }
        }

        return \array_values($displayers);
    }
}
