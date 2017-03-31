<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Log;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

interface Log extends PsrLoggerInterface
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
    public const MESSAGE = 'log.message';

    /**
     * Register a file log handler.
     *
     * @param string      $path
     * @param string      $level
     * @param object|null $processor
     * @param object|null $formatter
     *
     * @return void
     */
    public function useFiles(
        string $path,
        string $level = 'debug',
        $processor = null,
        $formatter = null
    );

    /**
     * Register a daily file log handler.
     *
     * @param string      $path
     * @param int         $days
     * @param string      $level
     * @param object|null $processor
     * @param object|null $formatter
     *
     * @return void
     */
    public function useDailyFiles(
        string $path,
        int $days = 0,
        string $level = 'debug',
        $processor = null,
        $formatter = null
    );
}
