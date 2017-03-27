<?php
declare(strict_types=1);
namespace Viserio\Component\Console;

final class ConsoleEvents
{
    /**
     * The COMMAND event allows you to attach listeners before any command is
     * executed by the console. It also allows you to modify the command, input and output
     * before they are handled to the command.
     *
     * @Event("Viserio\Component\Console\Event\ConsoleCommandEvent")
     *
     * @var string
     */
    public const COMMAND = 'console.command';

    /**
     * The TERMINATE event allows you to attach listeners after a command is
     * executed by the console.
     *
     * @Event("Viserio\Component\Console\Event\ConsoleTerminateEvent")
     *
     * @var string
     */
    public const TERMINATE = 'console.terminate';

    /**
     * The ERROR event occurs when an uncaught exception appears or
     * a throwable error.
     *
     * This event allows you to deal with the exception/error or
     * to modify the thrown exception.
     *
     * @Event("Viserio\Component\Console\Event\ConsoleErrorEvent")
     *
     * @var string
     */
    public const ERROR = 'console.error';
}
