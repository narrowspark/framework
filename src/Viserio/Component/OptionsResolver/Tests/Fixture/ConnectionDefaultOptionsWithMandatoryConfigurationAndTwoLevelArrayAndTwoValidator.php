<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

class ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayAndTwoValidator implements ProvidesDefaultOptionContract,
    RequiresConfigContract,
    RequiresMandatoryOptionContract,
    RequiresValidatedOptionContract
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
