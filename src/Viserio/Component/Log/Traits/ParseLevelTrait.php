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

namespace Viserio\Component\Log\Traits;

use Monolog\Logger as MonologLogger;
use Viserio\Contract\Log\Exception\InvalidArgumentException;

trait ParseLevelTrait
{
    /**
     * All of the error levels.
     *
     * @var array
     */
    protected static $levels = [
        'debug' => MonologLogger::DEBUG,
        'info' => MonologLogger::INFO,
        'notice' => MonologLogger::NOTICE,
        'warning' => MonologLogger::WARNING,
        'error' => MonologLogger::ERROR,
        'critical' => MonologLogger::CRITICAL,
        'alert' => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
    ];

    /**
     * Parse the string level into a Monolog constant.
     *
     * @throws \InvalidArgumentException
     */
    public static function parseLevel(string $level): int
    {
        if (isset(self::$levels[$level])) {
            return self::$levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level.');
    }
}
