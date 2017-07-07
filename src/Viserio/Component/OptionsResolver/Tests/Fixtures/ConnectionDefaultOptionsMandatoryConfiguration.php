<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectionDefaultOptionsMandatoryConfiguration implements RequiresComponentConfigContract, RequiresMandatoryOptionsContract, ProvidesDefaultOptionsContract
{
    public static function getDimensions(): iterable
    {
        return ['doctrine', 'connection', 'orm_default'];
    }

    public static function getMandatoryOptions(): iterable
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
}
