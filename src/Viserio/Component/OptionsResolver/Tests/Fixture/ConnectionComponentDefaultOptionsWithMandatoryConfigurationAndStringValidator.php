<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

class ConnectionComponentDefaultOptionsWithMandatoryConfigurationAndStringValidator implements
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract,
    ProvidesDefaultOptionsContract,
    RequiresValidatedConfigContract
{
    public static function getMandatoryOptions(): array
    {
        return ['driverClass'];
    }

    public static function getDefaultOptions(): array
    {
        return [
            'params' => [
                'host' => 'awesomehost',
                'port' => '4444',
            ],
        ];
    }

    public static function getOptionValidators(): array
    {
        return [
            'driverClass' => ['string'],
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): array
    {
        return ['vendor', 'package'];
    }
}
