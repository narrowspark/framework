<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Config\Tests\Fixture;

use RuntimeException;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;

class ConnectionDefaultConfigWithMandatoryConfigurationAndTwoLevelArrayValidator implements ProvidesDefaultConfigContract,
    RequiresConfigContract,
    RequiresMandatoryConfigContract,
    RequiresValidatedConfigContract
{
    public static function getMandatoryConfig(): iterable
    {
        return [
            'driverClass' => [
                'connection',
            ],
        ];
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
            'driverClass' => [
                'connection' => static function ($value): void {
                    if (! \is_string($value)) {
                        throw new RuntimeException('need to be a string.');
                    }
                },
            ],
        ];
    }
}
