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

use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;

class UniversalContainerIdComponentConfiguration implements ProvidesDefaultConfigContract, RequiresComponentConfigIdContract, RequiresMandatoryConfigContract
{
    /** @var array */
    private static $dimensions = [
        'doctrine',
        'universal',
    ];

    /** @var array */
    private static $getMandatoryConfig = [
        'params' => ['user', 'dbname'],
        'driverClass',
    ];

    /** @var array */
    private static $getDefaultConfig = [
        'params' => [
            'host' => 'awesomehost',
            'port' => '4444',
        ],
    ];

    public static function getDimensions(): iterable
    {
        return self::getData('dimensions');
    }

    public static function getMandatoryConfig(): iterable
    {
        return self::getData('getMandatoryConfig');
    }

    public static function getDefaultConfig(): iterable
    {
        return self::getData('getDefaultConfig');
    }

    private static function getData($name)
    {
        return self::${$name};
    }
}
