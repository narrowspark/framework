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

namespace Viserio\Component\Exception\Traits;

trait DetermineErrorLevelTrait
{
    /**
     * Determine if an error level is fatal (halts execution).
     *
     * @param int $level
     *
     * @return bool
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
