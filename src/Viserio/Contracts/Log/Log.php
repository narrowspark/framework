<?php
namespace Viserio\Contracts\Log;

use Closure;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

interface Log extends PsrLoggerInterface
{
    /**
     * Register a file log handler.
     *
     * @param string      $path
     * @param string      $level
     * @param object|null $processor
     * @param object|null $formatter
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
     */
    public function useDailyFiles(
        string $path,
        int $days = 0,
        string $level = 'debug',
        $processor = null,
        $formatter = null
    );

    /**
     * Register a new callback handler for when a log event is triggered.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function on(Closure $callback);
}
