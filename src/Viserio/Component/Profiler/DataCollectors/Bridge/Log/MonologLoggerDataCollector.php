<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollectors\Bridge\Log;

use ErrorException;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\Debug\Exception\SilencedErrorContext;
use Viserio\Component\Contracts\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Contracts\Profiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\Log\Writer;
use Viserio\Component\Profiler\DataCollectors\AbstractDataCollector;

class MonologLoggerDataCollector extends AbstractDataCollector implements
    TooltipAwareContract,
    PanelAwareContract
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
        if ($logger instanceof Logger || $logger instanceof Writer) {
            $this->logger = $logger;
        } else {
            throw new RuntimeException(sprintf(
                'Class [%s] or [%s] is required; Instance of [%s] given.',
                Logger::class,
                Writer::class,
                get_class($logger)
            ));
        }

        if ($this->getDebugLogger() === null) {
            throw new RuntimeException(sprintf('Processor %s is missing from %s', DebugProcessor::class, get_class($logger)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        $status = '';

        if ($this->getCountedErrors() !== 0) {
            $status = 'status-red';
        } elseif ($this->getCountedWarnings() !== 0) {
            $status = 'status-yellow';
        } elseif ($this->getCountedDeprecations() !== 0) {
            $status = 'status-yellow';
        }

        return [
            'class' => $status,
            'label' => 'Logs',
            'icon'  => 'ic_library_books_white_24px.svg',
            'value' => $this->data['counted'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        $html = $this->createTooltipGroup([
            'Errors'       => $this->getCountedErrors(),
            'Warnings'     => $this->getCountedWarnings(),
            'Deprecations' => $this->getCountedDeprecations(),
        ]);

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $html          = '';
        $tableHeaders  = [
            'Level',
            'Channel',
            'Message',
        ];
        $logs          = $this->groupLogLevels();

        $html = $this->createTabs([
            [
                'name'    => 'Info. & Errors <span class="counter">' . count($logs['info_error']) . '</span>',
                'content' => $this->createTable(
                    $logs['info_error'],
                    [
                        'headers'   => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ], [
                'name'    => 'Deprecations <span class="counter">' . $this->getCountedDeprecations() . '</span>',
                'content' => $this->createTable(
                    $logs['deprecation'],
                    [
                        'headers'   => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ], [
                'name'    => 'Debug <span class="counter">' . count($logs['debug']) . '</span>',
                'content' => $this->createTable(
                    $logs['debug'],
                    [
                        'headers'   => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ], [
                'name'    => 'Silenced PHP Notices <span class="counter">' . count($logs['silenced']) . '</span>',
                'content' => $this->createTable(
                    $logs['silenced'],
                    [
                        'headers'   => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ],
        ]);

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $data = $this->getComputedErrorsCount();

        $data['logs']    = $this->sanitizeLogs($this->getLogs());
        $data['counted'] = count($data['logs']);

        $this->data = $data;
    }

    /**
     * Get log error priorities.
     *
     * @return array
     */
    public function getPriorities(): array
    {
        return $this->data['priorities'] ?? [];
    }

    /**
     * Get counted errors.
     *
     * @return int
     */
    public function getCountedErrors(): int
    {
        return $this->data['error_count'] ?? 0;
    }

    /**
     * Get counted deprecations.
     *
     * @return int
     */
    public function getCountedDeprecations(): int
    {
        return $this->data['deprecation_count'] ?? 0;
    }

    /**
     * Get counted warnings.
     *
     * @return int
     */
    public function getCountedWarnings(): int
    {
        return $this->data['warning_count'] ?? 0;
    }

    /**
     * Get counted screams.
     *
     * @return int
     */
    public function getCountedScreams(): int
    {
        return $this->data['scream_count'] ?? 0;
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
     * Returns collected logs.
     *
     * @return array
     */
    public function getLogs(): array
    {
        if ($logger = $this->getDebugLogger()) {
            return $logger->getLogs();
        }

        return [];
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

    /**
     * Undocumented function.
     *
     * @param array $logs
     *
     * @return array
     */
    private function sanitizeLogs(array $logs): array
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

    /**
     * Undocumented function.
     *
     * @param array $log
     *
     * @return bool
     */
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

    /**
     * Get computed log error levels.
     *
     * @return array
     */
    private function getComputedErrorsCount(): array
    {
        $errorCount = 0;

        if ($logger = $this->getDebugLogger()) {
            $errorCount = $logger->countErrors();
        }

        $count = [
            'error_count'       => $errorCount,
            'deprecation_count' => 0,
            'warning_count'     => 0,
            'scream_count'      => 0,
            'priorities'        => [],
        ];

        foreach ($this->getLogs() as $log) {
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

    /**
     * Group log level together.
     *
     * @return array
     */
    private function groupLogLevels(): array
    {
        $deprecationLogs  = [];
        $debugLogs        = [];
        $infoAndErrorLogs = [];
        $silencedLogs     = [];

        $formatLog = function ($log) {
            return[
                $log['priorityName'] . '<br>' . '<div class="text-muted">' . date('H:i:s', $log['timestamp']) . '</div>',
                $log['channel'],
                $log['message'] . '<br>' . (! empty($log['context']) ? $this->cloneVar($log['context']) : ''),
            ];
        };

        foreach ($this->data['logs'] as $log) {
            if (isset($log['priority']) && (in_array($log['priority'], [Logger::ERROR, Logger::INFO]))) {
                $infoAndErrorLogs[] = $formatLog($log);
            } elseif (isset($log['priority']) && $log['priority'] === Logger::DEBUG) {
                $debugLogs[] = $formatLog($log);
            } elseif ($this->isSilencedOrDeprecationErrorLog($log)) {
                if (isset($log['context']) && $log['context']['exception'] instanceof SilencedErrorContext) {
                    $silencedLogs[] = $formatLog($log);
                } else {
                    $deprecationLogs[] = $formatLog($log);
                }
            }
        }

        return [
            'deprecation' => $deprecationLogs,
            'debug'       => $debugLogs,
            'info_error'  => $infoAndErrorLogs,
            'silenced'    => $silencedLogs,
        ];
    }
}
