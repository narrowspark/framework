<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class UniversalContainerIdComponentConfiguration implements RequiresComponentConfigIdContract, ProvidesDefaultOptionsContract, RequiresMandatoryOptionsContract
{
    /**
     * @var array
     */
    private static $dimensions = [
        'doctrine',
        'universal',
    ];

    /**
     * @var array
     */
    private static $getMandatoryOptions = [
        'params' => ['user', 'dbname'],
        'driverClass',
    ];

    /**
     * @var array
     */
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
