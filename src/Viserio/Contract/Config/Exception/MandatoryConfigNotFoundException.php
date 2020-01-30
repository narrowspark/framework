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

namespace Viserio\Contract\Config\Exception;

use OutOfBoundsException;
use Throwable;

class MandatoryConfigNotFoundException extends OutOfBoundsException implements Exception
{
    /**
     * Create a new MandatoryOptionNotFound exception.
     *
     * @param iterable       $dimensions
     * @param string         $option
     * @param int            $code
     * @param null|Throwable $previous
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
