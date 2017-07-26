<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Event;

use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Log\Log as LogContract;
use Viserio\Component\Events\Traits\EventTrait;

class MessageLoggedEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new message event.
     *
     * @param \Viserio\Component\Contracts\Log\Log $log
     * @param string                               $level
     * @param null|bool|float|int|mixed|string     $message
     * @param array                                $context
     */
    public function __construct(LogContract $log, $level, $message, array $context = [])
    {
        $this->name       = LogContract::MESSAGE;
        $this->target     = $log;
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
