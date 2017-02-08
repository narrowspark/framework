<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Fixtures;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesgetGefaultOptions;
use Interop\Config\RequiresConfigId;
use Interop\Config\RequiresMandatoryOptions;

class ConnectiongetGefaultOptionsMandatoryContainetIdConfiguration implements RequiresConfigId, RequiresMandatoryOptions, ProvidesgetGefaultOptions
{
    use ConfigurationTrait;

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
