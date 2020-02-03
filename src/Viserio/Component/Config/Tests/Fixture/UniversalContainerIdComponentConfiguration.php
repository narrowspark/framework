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
    private static $getMandatoryOptions = [
        'params' => ['user', 'dbname'],
        'driverClass',
    ];

    /** @var array */
    private static $getDefaultOptions = [
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
        return self::getData('getMandatoryOptions');
    }

    public static function getDefaultConfig(): iterable
    {
        return self::getData('getDefaultOptions');
    }

    private static function getData($name)
    {
        return self::${$name};
    }
}
