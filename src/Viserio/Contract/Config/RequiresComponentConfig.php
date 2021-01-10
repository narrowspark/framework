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

interface RequiresComponentConfig extends RequiresConfig
{
    /**
     * Returns the depth of the configuration array as a list. Can also be an empty array.
     * For instance, the structure of the getDimensions() method would be an value based array like.
     *
     * <code>
     *     return ['viserio', 'component', 'view'];
     * </code>
     *
     * @return array
     */
    public static function getDimensions(): iterable;
}
