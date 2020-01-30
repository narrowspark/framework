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

namespace Viserio\Component\Config\Tests\Fixture;

use RuntimeException;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;

class ConnectionDefaultOptionsWithMandatoryConfigurationAndValidator implements ProvidesDefaultConfigContract,
    RequiresConfigContract,
    RequiresMandatoryConfigContract,
    RequiresValidatedConfigContract
{
    public static function getMandatoryConfig(): iterable
    {
        return ['driverClass'];
    }

    public static function getDefaultConfig(): iterable
    {
        return [
            'params' => [
                'host' => 'awesomehost',
                'port' => '4444',
            ],
        ];
    }

    public static function getConfigValidators(): iterable
    {
        return [
            'driverClass' => static function ($value): void {
                if (! \is_string($value)) {
                    throw new RuntimeException('need to be a string.');
                }
            },
        ];
    }
}
