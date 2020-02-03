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

interface ProvidesDefaultConfig
{
    /**
     * Returns a list of default config for this class.
     *
     * @return array list with default options and values, can be nested
     */
    public static function getDefaultConfig(): iterable;
}
