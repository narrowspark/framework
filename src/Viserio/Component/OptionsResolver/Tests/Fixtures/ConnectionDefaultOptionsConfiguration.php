<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;

class ConnectiongetGefaultOptionsConfiguration implements RequiresConfigContract, ProvidesgetGefaultOptions
{
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
