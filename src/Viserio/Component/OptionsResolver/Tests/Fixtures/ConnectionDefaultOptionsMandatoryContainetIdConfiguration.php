<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectionDefaultOptionsMandatoryContainetIdConfiguration implements RequiresComponentConfigIdContract, RequiresMandatoryOptionsContract, ProvidesDefaultOptionsContract
{
    public static function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
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
