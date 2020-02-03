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

namespace Viserio\Component\Config\Tests\Fixture;

use Viserio\Contract\Config\DeprecatedConfig as DeprecatedOptionsContract;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;

class ConnectionComponentDefaultConfigWithMultiDimensionalDeprecationKeyConfiguration implements DeprecatedOptionsContract, ProvidesDefaultConfigContract, RequiresComponentConfigContract
{
    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getDeprecatedConfig(): iterable
    {
        return [
            'params' => [
                'host',
            ],
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'params' => [
                'host' => 'awesomehost',
                'port' => '4444',
            ],
        ];
    }
}
