<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Log;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

interface Log extends PsrLoggerInterface
{
    /**
     * The MESSAGE event allows you building profilers or other tools
     * that aggregate all of the log messages for a given "request" cycle.
     *
     * @var string
     */
    public const MESSAGE = 'log.message';

    /**
     * Register a file log handler.
     *
     * @param string              $path
     * @param string              $level
     * @param null|callable|array $processors
     * @param null|object|string  $formatter
     *
     * @return void
     */
    public function useFiles(
        string $path,
        string $level = 'debug',
        $processors = null,
        $formatter = null
    ): void;

    /**
     * Register a daily file log handler.
     *
     * @param string              $path
     * @param int                 $days
     * @param string              $level
     * @param null|callable|array $processors
     * @param null|object|string  $formatter
     *
     * @return void
     */
    public function useDailyFiles(
        string $path,
        int $days = 0,
        string $level = 'debug',
        $processors = null,
        $formatter = null
    ): void;
}
