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

interface DeprecatedConfig
{
    /**
     * Deprecate config key that will be no more used in the next version.
     * Key should be available in getMandatoryOptions or getDefaultOptions.
     *
     * The deprecation message supports a sprintf replacer for the key.
     *
     * @return array
     */
    public static function getDeprecatedConfig(): iterable;
}
