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

namespace Viserio\Component\Config\Tests\Fixture;

use Viserio\Contract\Config\DeprecatedConfig as DeprecatedConfigContract;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;

class ConnectionComponentDefaultConfigMandatoryContainedIdWithDeprecationKeyConfiguration implements DeprecatedConfigContract, ProvidesDefaultConfigContract, RequiresComponentConfigIdContract, RequiresMandatoryConfigContract
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
    public static function getMandatoryConfig(): iterable
    {
        return ['driverClass'];
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

    /**
     * {@inheritdoc}.
     */
    public static function getDeprecatedConfig(): iterable
    {
        return [
            'driverClass',
        ];
    }
}
