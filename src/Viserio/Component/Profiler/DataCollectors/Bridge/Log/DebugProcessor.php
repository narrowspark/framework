<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollectors\Bridge\Log;

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

    public function __invoke(array $record)
    {
        $this->records[] = [
            'timestamp'    => $record['datetime']->getTimestamp(),
            'message'      => $record['message'],
            'priority'     => $record['level'],
            'priorityName' => $record['level_name'],
            'context'      => $record['context'],
            'channel'      => $record['channel'] ?? '',
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
     *
     * @return array
     */
    public function getLogs(): array
    {
        return $this->records;
    }

    /**
     * Returns the number of errors.
     *
     * @return int
     */
    public function countErrors(): int
    {
        return $this->errorCount;
    }
}
