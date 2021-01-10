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

namespace Viserio\Component\Log\Event;

use Psr\Log\LoggerInterface;
use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Component\Log\Logger;
use Viserio\Contract\Events\Event as EventContract;

class MessageLoggedEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new message event.
     *
     * @param null|bool|float|int|mixed|string $message
     */
    public function __construct(LoggerInterface $log, string $level, $message, array $context = [])
    {
        $this->name = Logger::MESSAGE;
        $this->target = $log;
        $this->parameters = ['level' => $level, 'message' => $message, 'context' => $context];
    }

    /**
     * The log "level".
     *
     * @var string
     */
    public function getLevel(): string
    {
        return $this->parameters['level'];
    }

    /**
     * The log message.
     *
     * @var null|bool|float|int|mixed|string
     */
    public function getMessage()
    {
        return $this->parameters['message'];
    }

    /**
     * The log context.
     *
     * @var array
     */
    public function getContext(): array
    {
        return $this->parameters['context'];
    }
}
