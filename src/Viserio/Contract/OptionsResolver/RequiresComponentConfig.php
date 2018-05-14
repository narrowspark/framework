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

namespace Viserio\Contract\OptionsResolver;

/**
 * Code in this interface is taken from interop-config.
 *
 * @author Sandro Keil https://sandro-keil.de/blog/
 * @copyright Copyright (c) 2015-2017 Sandro Keil
 */
interface RequiresComponentConfig extends RequiresConfig
{
    /**
     * Returns the depth of the configuration array as a list. Can also be an empty array. For instance, the structure
     * of the getDimensions() method would be an array like.
     *
     * <code>
     *     return ['viserio', 'component', 'view'];
     * </code>
     *
     * @return array
     */
    public static function getDimensions(): array;
}
