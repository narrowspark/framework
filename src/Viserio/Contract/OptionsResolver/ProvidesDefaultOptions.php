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
 * Code in this trait is taken from interop-config.
 *
 * @author Sandro Keil https://sandro-keil.de/blog/
 * @copyright Copyright (c) 2015-2017 Sandro Keil
 */
interface ProvidesDefaultOptions
{
    /**
     * Returns a list of default options, which are
     * merged in \Viserio\Component\OptionsResolver\Traits\AbstractOptionsResolverTrait::getResolvedConfig().
     *
     * @return array list with default options and values, can be nested
     */
    public static function getDefaultOptions(): array;
}
