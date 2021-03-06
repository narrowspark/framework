<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Log\DataCollector;

use ErrorException;
use Monolog\Logger as MonologLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Debug\Exception\SilencedErrorContext;
use Viserio\Bridge\Monolog\Processor\DebugProcessor;
use Viserio\Component\Log\Logger;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;
use Viserio\Contract\Profiler\Exception\RuntimeException;
use Viserio\Contract\Profiler\Exception\UnexpectedValueException;
use Viserio\Contract\Profiler\PanelAware as PanelAwareContract;
use Viserio\Contract\Profiler\TooltipAware as TooltipAwareContract;

class LoggerDataCollector extends AbstractDataCollector implements PanelAwareContract,
    TooltipAwareContract
{
    /**
     * Monolog logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create a new logs data collector instance.
     *
     * @param \Monolog\Logger|\Viserio\Component\Log\Logger $logger
     *
     * @throws \Viserio\Contract\Profiler\Exception\UnexpectedValueException if wrong class is given
     * @throws \Viserio\Contract\Profiler\Exception\RuntimeException
     */
    public function __construct($logger)
    {
        if ($logger instanceof MonologLogger) {
            $this->logger = $logger;
        } elseif ($logger instanceof Logger) {
            $this->logger = $logger->getMonolog();
        } else {
            throw new UnexpectedValueException(\sprintf('Class [%s] or [%s] is required; Instance of [%s] given.', MonologLogger::class, Logger::class, (\is_object($logger) ? \get_class($logger) : \gettype($logger))));
        }

        if ($this->getDebugLogger() === null) {
            throw new RuntimeException(\sprintf('Processor %s is missing from %s.', DebugProcessor::class, \get_class($logger)));
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
        } elseif ($this->getCountedWarnings() !== 0 || $this->getCountedDeprecations() !== 0) {
            $status = 'status-yellow';
        }

        return [
            'class' => $status,
            'label' => 'Logs',
            'icon' => 'ic_library_books_white_24px.svg',
            'value' => $this->data['counted'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        return $this->createTooltipGroup([
            'Errors' => $this->getCountedErrors(),
            'Warnings' => $this->getCountedWarnings(),
            'Deprecations' => $this->getCountedDeprecations(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $tableHeaders = ['Level', 'Channel', 'Message'];

        $logs = $this->groupLogLevels();

        return $this->createTabs([
            [
                'name' => 'Info. & Errors <span class="counter">' . \count($logs['info_error']) . '</span>',
                'content' => $this->createTable(
                    $logs['info_error'],
                    [
                        'headers' => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ], [
                'name' => 'Deprecations <span class="counter">' . $this->getCountedDeprecations() . '</span>',
                'content' => $this->createTable(
                    $logs['deprecation'],
                    [
                        'headers' => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ], [
                'name' => 'Debug <span class="counter">' . \count($logs['debug']) . '</span>',
                'content' => $this->createTable(
                    $logs['debug'],
                    [
                        'headers' => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ], [
                'name' => 'Silenced PHP Notices <span class="counter">' . \count($logs['silenced']) . '</span>',
                'content' => $this->createTable(
                    $logs['silenced'],
                    [
                        'headers' => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $data = $this->getComputedErrorsCount();

        $data['logs'] = $this->sanitizeLogs($this->getLogs());
        $data['counted'] = \count($data['logs']);

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
     * {@inheritdoc}
     */
    public function reset(): void
    {
        if (($logger = $this->getDebugLogger()) && \method_exists($logger, 'reset')) {
            $logger->reset();
        }
    }

    /**
     * Returns a DebugProcessor instance if one is registered with this logger.
     *
     * @return null|\Viserio\Bridge\Monolog\Processor\DebugProcessor
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
            $errorId = \md5("{$exception->getSeverity()}/{$exception->getLine()}/{$exception->getFile()}\0{$log['message']}", true);

            if (isset($sanitizedLogs[$errorId])) {
                $sanitizedLogs[$errorId]['errorCount']++;
            } else {
                $log += [
                    'errorCount' => 1,
                    'scream' => $exception instanceof SilencedErrorContext,
                ];

                $sanitizedLogs[$errorId] = $log;
            }
        }

        return \array_values($sanitizedLogs);
    }

    /**
     * Find silenced or deprecation in error log.
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

        if ($exception instanceof ErrorException && \in_array($exception->getSeverity(), [\E_DEPRECATED, \E_USER_DEPRECATED], true)) {
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
            'error_count' => $errorCount,
            'deprecation_count' => 0,
            'warning_count' => 0,
            'scream_count' => 0,
            'priorities' => [],
        ];

        foreach ($this->getLogs() as $log) {
            if (isset($count['priorities'][$log['priority']])) {
                $count['priorities'][$log['priority']]['count']++;
            } else {
                $count['priorities'][$log['priority']] = [
                    'count' => 1,
                    'name' => $log['priorityName'],
                ];
            }

            if ('WARNING' === $log['priorityName']) {
                $count['warning_count']++;
            }

            if ($this->isSilencedOrDeprecationErrorLog($log)) {
                if ($log['context']['exception'] instanceof SilencedErrorContext) {
                    $count['scream_count']++;
                } else {
                    $count['deprecation_count']++;
                }
            }
        }

        \ksort($count['priorities']);

        return $count;
    }

    /**
     * Group log level together.
     *
     * @return array
     */
    private function groupLogLevels(): array
    {
        $deprecationLogs = [];
        $debugLogs = [];
        $infoAndErrorLogs = [];
        $silencedLogs = [];

        $formatLog = function ($log) {
            return [
                $log['priorityName'] . '<br><div class="text-muted">' . \date('H:i:s', $log['timestamp']) . '</div>',
                $log['channel'],
                $log['message'] . '<br>' . (\count($log['context']) !== 0 ? $this->cloneVar($log['context']) : ''),
            ];
        };

        foreach ((array) $this->data['logs'] as $log) {
            if (isset($log['priority'])) {
                if (\in_array($log['priority'], [Logger::ERROR, Logger::INFO], true)) {
                    $infoAndErrorLogs[] = $formatLog($log);
                } elseif ($log['priority'] === Logger::DEBUG) {
                    $debugLogs[] = $formatLog($log);
                }
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
            'debug' => $debugLogs,
            'info_error' => $infoAndErrorLogs,
            'silenced' => $silencedLogs,
        ];
    }
}
