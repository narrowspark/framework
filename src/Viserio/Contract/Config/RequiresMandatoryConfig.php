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
     * Return mandatory config which must be available.
     *
     * @return array mandatory config, can be nested
     */
    public static function getMandatoryConfig(): iterable;
}
