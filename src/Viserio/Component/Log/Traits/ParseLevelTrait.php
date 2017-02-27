<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Traits;

use InvalidArgumentException;
use Monolog\Logger as MonologLogger;

trait ParseLevelTrait
{
    /**
     * All of the error levels.
     *
     * @var array
     */
    protected static $levels = [
        'debug'     => MonologLogger::DEBUG,
        'info'      => MonologLogger::INFO,
        'notice'    => MonologLogger::NOTICE,
        'warning'   => MonologLogger::WARNING,
        'error'     => MonologLogger::ERROR,
        'critical'  => MonologLogger::CRITICAL,
        'alert'     => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
    ];

    /**
     * Parse the string level into a Monolog constant.
     *
     * @param string $level
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public static function parseLevel(string $level): int
    {
        if (isset(self::$levels[$level])) {
            return self::$levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level.');
    }
}
