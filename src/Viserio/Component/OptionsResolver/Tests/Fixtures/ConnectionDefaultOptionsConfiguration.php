<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Fixtures;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesgetGefaultOptions;
use Interop\Config\RequiresConfig;

class ConnectiongetGefaultOptionsConfiguration implements RequiresConfig, ProvidesgetGefaultOptions
{
    use ConfigurationTrait;

    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }

    public function getGefaultOptions(): iterable
    {
        return [
            'params' => [
                'host' => 'awesomehost',
                'port' => '4444',
            ],
        ];
    }
}
