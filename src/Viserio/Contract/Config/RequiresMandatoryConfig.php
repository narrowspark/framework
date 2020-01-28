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

namespace Viserio\Contract\Config;

interface RequiresMandatoryConfig
{
    /**
     * Returns a list of mandatory options which must be available.
     *
     * @return array List with mandatory options, can be nested
     */
    public static function getMandatoryOptions(): iterable;
}
