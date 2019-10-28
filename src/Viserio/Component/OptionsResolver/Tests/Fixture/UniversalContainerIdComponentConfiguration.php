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

namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;

class UniversalContainerIdComponentConfiguration implements ProvidesDefaultOptionContract, RequiresComponentConfigIdContract, RequiresMandatoryOptionContract
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

    public static function getDimensions(): array
    {
        return self::getData('dimensions');
    }

    public static function getMandatoryOptions(): array
    {
        return self::getData('getMandatoryOptions');
    }

    public static function getDefaultOptions(): array
    {
        return self::getData('getDefaultOptions');
    }

    private static function getData($name)
    {
        return self::${$name};
    }
}
