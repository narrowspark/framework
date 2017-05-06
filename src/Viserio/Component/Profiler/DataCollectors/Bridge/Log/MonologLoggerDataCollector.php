<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollectors\Bridge\Log;

use ErrorException;
use Monolog\Logger;
use Symfony\Component\Debug\Exception\SilencedErrorContext;
use Viserio\Component\Contracts\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Profiler\DataCollectors\MessagesDataCollector;

class MonologLoggerDataCollector extends MessagesDataCollector implements PanelAwareContract
{
    /**
     * Monolog logger instance.
     *
     * @var \Monolog\Logger|\Viserio\Component\Log\Writer
     */
    protected $logger;

    /**
     * Create a new logs data collector instance.
     *
     * @param \Monolog\Logger|\Viserio\Component\Log\Writer $logger
     */
    public function __construct($logger)
    {
        parent::__construct('logs');

        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'label' => 'Logs',
            'value' => $this->data['counted'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $html = '';

        $messages = $this->sanitizeLogs($this->getMessages());

        return $html;
    }

    /**
     * Get counted logs.
     *
     * @return int
     */
    public function getCountedLogs(): int
    {
        return $this->data['counted'] ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(): array
    {
        if ($logger = $this->getDebugLogger()) {
            return $logger->getLogs();
        }

        return [];
    }

    /**
     * Returns the number of errors.
     *
     * @return int
     */
    private function getCountedErrors(): int
    {
        if ($logger = $this->getDebugLogger()) {
            return $logger->countErrors();
        }

        return 0;
    }

    /**
     * Returns a DebugProcessor instance if one is registered with this logger.
     *
     * @return Viserio\Component\Profiler\DataCollectors\Bridge\Log\DebugProcessor|null
     */
    private function getDebugLogger(): ?DebugProcessor
    {
        foreach ($this->logger->getProcessors() as $processor) {
            if ($processor instanceof DebugProcessor) {
                return $processor;
            }
        }

        return null;
    }

    private function sanitizeLogs(array $logs)
    {
        $sanitizedLogs = [];

        foreach ($logs as $log) {
            if (! $this->isSilencedOrDeprecationErrorLog($log)) {
                $sanitizedLogs[] = $log;
                continue;
            }

            $exception = $log['context']['exception'];
            $errorId   = md5("{$exception->getSeverity()}/{$exception->getLine()}/{$exception->getFile()}\0{$log['message']}", true);

            if (isset($sanitizedLogs[$errorId])) {
                ++$sanitizedLogs[$errorId]['errorCount'];
            } else {
                $log += [
                    'errorCount' => 1,
                    'scream'     => $exception instanceof SilencedErrorContext,
                ];

                $sanitizedLogs[$errorId] = $log;
            }
        }

        return array_values($sanitizedLogs);
    }

    private function isSilencedOrDeprecationErrorLog(array $log): bool
    {
        if (! isset($log['context']['exception'])) {
            return false;
        }

        $exception = $log['context']['exception'];

        if ($exception instanceof SilencedErrorContext) {
            return true;
        }

        if ($exception instanceof ErrorException && in_array($exception->getSeverity(), [E_DEPRECATED, E_USER_DEPRECATED], true)) {
            return true;
        }

        return false;
    }

    private function computeErrorsCount()
    {
        $count = [
            'error_count'       => $this->getCountedErrors(),
            'deprecation_count' => 0,
            'warning_count'     => 0,
            'scream_count'      => 0,
            'priorities'        => [],
        ];

        foreach ($this->getMessages() as $log) {
            if (isset($count['priorities'][$log['priority']])) {
                ++$count['priorities'][$log['priority']]['count'];
            } else {
                $count['priorities'][$log['priority']] = [
                    'count' => 1,
                    'name'  => $log['priorityName'],
                ];
            }

            if ('WARNING' === $log['priorityName']) {
                ++$count['warning_count'];
            }

            if ($this->isSilencedOrDeprecationErrorLog($log)) {
                if ($log['context']['exception'] instanceof SilencedErrorContext) {
                    ++$count['scream_count'];
                } else {
                    ++$count['deprecation_count'];
                }
            }
        }

        ksort($count['priorities']);

        return $count;
    }
}
