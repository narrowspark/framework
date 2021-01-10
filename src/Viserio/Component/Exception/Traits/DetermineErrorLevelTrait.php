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

namespace Viserio\Component\Exception\Traits;

trait DetermineErrorLevelTrait
{
    /**
     * Determine if an error level is fatal (halts execution).
     */
    protected static function isLevelFatal(int $level): bool
    {
        $errors = \E_ERROR;
        $errors |= \E_PARSE;
        $errors |= \E_CORE_ERROR;
        $errors |= \E_CORE_WARNING;
        $errors |= \E_COMPILE_ERROR;
        $errors |= \E_COMPILE_WARNING;

        return ($level & $errors) > 0;
    }
}
