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

use Viserio\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

class ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoValidator implements ProvidesDefaultOptionsContract,
    RequiresConfigContract,
    RequiresMandatoryOptionsContract,
    RequiresValidatedConfigContract
{
    public static function getMandatoryOptions(): array
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

    public static function getOptionValidators(): array
    {
        return [
            'driverClass' => static function ($value): void {
                if (! \is_string($value)) {
                    throw new \RuntimeException('need to be a string.');
                }
            },
            'test' => static function ($value): void {
                if (! \is_string($value)) {
                    throw new \RuntimeException('need to be a string.');
                }
            },
        ];
    }
}
