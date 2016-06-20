<?php
namespace Viserio\Contracts\Logging;

interface Log
{
    /**
     * Register a file log handler.
     *
     * @param string      $path
     * @param string      $level
     * @param object|null $processor
     * @param object|null $formatter
     */
    public function useFiles(string $path, string $level = 'debug', $processor = null, $formatter = null);

    /**
     * Register a daily file log handler.
     *
     * @param string      $path
     * @param int         $days
     * @param string      $level
     * @param object|null $processor
     * @param object|null $formatter
     */
    public function useDailyFiles($path, int $days = 0, string $level = 'debug', $processor = null, $formatter = null);
}
