<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesgetGefaultOptions;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;

class ConnectiongetGefaultOptionsMandatoryConfiguration implements RequiresConfig, RequiresMandatoryOptions, ProvidesgetGefaultOptions
{
    use ConfigurationTrait;

    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection', 'orm_default'];
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
