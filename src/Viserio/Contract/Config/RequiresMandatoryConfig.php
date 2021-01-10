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
