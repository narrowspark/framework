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

use OutOfBoundsException;
use Throwable;

class MandatoryConfigNotFoundException extends OutOfBoundsException implements Exception
{
    /**
     * Create a new MandatoryOptionNotFound exception.
     */
    public function __construct(iterable $dimensions, string $option, int $code = 0, ?Throwable $previous = null)
    {
        $depth = '';

        foreach ($dimensions as $dimension) {
            if ($depth !== '') {
                $depth .= '.' . $dimension;
            } else {
                $depth .= $dimension;
            }
        }

        parent::__construct(
            \sprintf(
                'Mandatory option [%s] was not set for configuration [%s].',
                $option,
                $depth
            ),
            $code,
            $previous
        );
    }
}
