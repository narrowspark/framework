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

namespace Viserio\Component\Log;

use Monolog\Logger as Monolog;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Viserio\Component\Log\Event\MessageLoggedEvent;
use Viserio\Component\Log\Traits\ParseLevelTrait;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Contract\Support\Arrayable;
use Viserio\Contract\Support\Jsonable;

class Logger extends LogLevel implements PsrLoggerInterface
{
    use ParseLevelTrait;
    use EventManagerAwareTrait;
    use LoggerTrait;

    /**
     * The MESSAGE event allows you building profilers or other tools
     * that aggregate all of the log messages for a given "request" cycle.
     *
     * @var string
     */
    public const MESSAGE = 'log.message';

    /**
     * The handler parser instance.
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Create a new log writer instance.
     */
    public function __construct(Monolog $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Call Monolog with the given method and parameters.
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function __call(string $method, array $parameters)
    {
        return $this->logger->{$method}(...$parameters);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = []): void
    {
        $message = $this->formatMessage($message);

        if ($this->eventManager !== null) {
            // If the event dispatcher is set, we will pass along the parameters to the
            // log listeners. These are useful for building profilers or other tools
            // that aggregate all of the log messages for a given "request" cycle.
            $this->eventManager->trigger(
                new MessageLoggedEvent($this, $level, $message, $context)
            );
        }

        if (! \method_exists($this->logger, $level)) {
            throw new InvalidArgumentException(\sprintf('Call to undefined method \Monolog\Logger::%s.', $level));
        }

        $this->logger->{$level}($message, $context);
    }

    /**
     * Get the underlying Monolog instance.
     */
    public function getMonolog(): Monolog
    {
        return $this->logger;
    }

    /**
     * Format the parameters for the logger.
     *
     * @return null|bool|float|int|object|string
     */
    private function formatMessage($message)
    {
        if (\is_array($message)) {
            return \var_export($message, true);
        }

        /** @codeCoverageIgnoreStart */
        if ($message instanceof Jsonable) {
            return $message->toJson();
        }

        if ($message instanceof Arrayable) {
            return \var_export($message->toArray(), true);
        }
        /** @codeCoverageIgnoreEnd */

        return $message;
    }
}
