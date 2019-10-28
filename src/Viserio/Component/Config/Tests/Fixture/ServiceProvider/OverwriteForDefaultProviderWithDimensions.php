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

namespace Viserio\Component\Config\Tests\Fixture\ServiceProvider;

use Viserio\Contract\OptionsResolver\ProvidesDefaultOption;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

class OverwriteForDefaultProviderWithDimensions implements ProvidesDefaultOption, RequiresComponentConfigContract
{
    /**
     * Returns a list of default options, which are
     * merged in \Viserio\Component\OptionsResolver\Traits\AbstractOptionsResolverTrait::getResolvedConfig().
     *
     * @return array list with default options and values, can be nested
     */
    public static function getDefaultOptions(): array
    {
        return [
            'foo' => 'bar',
            'baz' => 'new value',
            'webserver' => [
                'debug_server' => [
                    'host' => 'tcp://127.0.0.1:9912',
                ],
            ],
        ];
    }

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
    public static function getDimensions(): array
    {
        return ['narrowspark'];
    }
}
