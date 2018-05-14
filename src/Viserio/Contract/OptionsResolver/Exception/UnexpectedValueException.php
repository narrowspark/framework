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

namespace Viserio\Contract\OptionsResolver\Exception;

use Throwable;
use UnexpectedValueException as BaseUnexpectedValueException;

class UnexpectedValueException extends BaseUnexpectedValueException implements Exception
{
    /**
     * Create a new UnexpectedValue exception.
     *
     * @param iterable        $dimensions
     * @param mixed           $currentDimension Current configuration key
     * @param int             $code
     * @param null|\Throwable $previous
     */
    public function __construct(
        iterable $dimensions,
        $currentDimension = null,
        int $code = 0,
        Throwable $previous = null
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
