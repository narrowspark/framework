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

namespace Viserio\Contract\Config\Exception;

use Throwable;
use UnexpectedValueException as BaseUnexpectedValueException;

class UnexpectedValueException extends BaseUnexpectedValueException implements Exception
{
    /**
     * Create a new UnexpectedValue exception.
     *
     * @param mixed $currentDimension Current configuration key
     */
    public function __construct(
        iterable $dimensions,
        $currentDimension = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $position = [];

        foreach ($dimensions as $dimension) {
            if ($dimension === $currentDimension) {
                break;
            }

            $position[] = $dimension;
        }

        parent::__construct(
            \sprintf(
                'Configuration must either be of type [array] or implement [\ArrayAccess]. '
                . 'Configuration position is [%s].',
                \rtrim(\implode('.', $position), '.')
            ),
            $code,
            $previous
        );
    }
}
