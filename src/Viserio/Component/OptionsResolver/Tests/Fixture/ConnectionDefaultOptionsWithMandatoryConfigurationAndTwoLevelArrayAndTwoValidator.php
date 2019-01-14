<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

class ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayAndTwoValidator implements
    RequiresConfigContract,
    RequiresMandatoryOptionsContract,
    ProvidesDefaultOptionsContract,
    RequiresValidatedConfigContract
{
    public static function getMandatoryOptions(): array
    {
        return [
            'driverClass' => [
                'connection',
            ],
            'orm' => [
                'default_connection',
            ],
        ];
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
            'driverClass' => [
                'connection' => static function ($value): void {
                    if (! \is_string($value)) {
                        throw new \RuntimeException('need to be a string.');
                    }
                },
            ],
            'orm' => [
                'default_connection' => static function ($value): void {
                    if (! \is_string($value)) {
                        throw new \RuntimeException('need to be a string.');
                    }
                },
            ],
        ];
    }
}
