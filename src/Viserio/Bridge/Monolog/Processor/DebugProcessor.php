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

namespace Viserio\Bridge\Monolog\Processor;

use Monolog\Logger;

class DebugProcessor
{
    /**
     * An array of logs.
     *
     * @var array
     */
    private $records = [];

    /**
     *  The number of errors.
     *
     * @var int
     */
    private $errorCount = 0;

    /**
     * Invoke the debug processor.
     */
    public function __invoke(array $record): array
    {
        $this->records[] = [
            'timestamp' => $record['datetime']->getTimestamp(),
            'message' => $record['message'],
            'priority' => $record['level'],
            'priorityName' => $record['level_name'],
            'context' => $record['context'],
            'channel' => $record['channel'] ?? '',
        ];

        switch ($record['level']) {
            case Logger::ERROR:
            case Logger::CRITICAL:
            case Logger::ALERT:
            case Logger::EMERGENCY:
                ++$this->errorCount;
        }

        return $record;
    }

    /**
     * Returns an array of logs.
     *
     * A log is an array with the following mandatory keys:
     * timestamp, message, priority, and priorityName.
     * It can also have an optional context key containing an array.
     */
    public function getLogs(): array
    {
        return $this->records;
    }

    /**
     * Returns the number of errors.
     */
    public function countErrors(): int
    {
        return $this->errorCount;
    }

    /**
     * Removes all log records.
     */
    public function reset(): void
    {
        $this->records = [];
        $this->errorCount = 0;
    }
}
