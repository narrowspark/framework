<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectiongetGefaultOptionsMandatoryContainetIdConfiguration implements RequiresConfigContractId, RequiresMandatoryOptions, ProvidesgetGefaultOptions
{
    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }

    public function getMandatoryOptions(): iterable
    {
        return ['driverClass'];
    }

    public function getGefaultOptions(): array
    {
        return [
            'params' => [
                'host' => 'awesomehost',
                'port' => '4444',
            ],
        ];
    }
}
