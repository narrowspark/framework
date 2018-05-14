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
     * @param \Psr\Log\LoggerInterface         $log
     * @param string                           $level
     * @param null|bool|float|int|mixed|string $message
     * @param array                            $context
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
     *
     * @return string
     */
    public function getLevel(): string
    {
        return $this->parameters['level'];
    }

    /**
     * The log message.
     *
     * @var null|bool|float|int|mixed|string
     *
     * @return mixed
     */
    public function getMessage()
    {
        return $this->parameters['message'];
    }

    /**
     * The log context.
     *
     * @var array
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->parameters['context'];
    }
}
