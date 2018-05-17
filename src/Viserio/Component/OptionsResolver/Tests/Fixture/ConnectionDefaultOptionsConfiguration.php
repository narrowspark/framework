<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

class ConnectionDefaultOptionsConfiguration implements RequiresComponentConfigContract, ProvidesDefaultOptionsContract
{
    public static function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }

    public static function getDefaultOptions(): iterable
    {
        return [
            'params' => [
                'host' => 'awesomehost',
                'port' => '4444',
            ],
        ];
    }
}
