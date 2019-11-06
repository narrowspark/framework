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

namespace Viserio\Component\Foundation\CacheWarmer;

class CacheWarmerAggregate
{
    /** @var iterable */
    private $warmers;

    /** @var bool */
    private $debug;

    /** @var null|string */
    private $deprecationLogsFilepath;

    /** @var bool */
    private $optionalsEnabled = false;

    /** @var bool */
    private $onlyOptionalsEnabled = false;

    /**
     * Create a new CacheWarmerAggregate instance.
     *
     * @param iterable    $warmers
     * @param bool        $debug
     * @param null|string $deprecationLogsFilepath
     */
    public function __construct(iterable $warmers = [], bool $debug = false, ?string $deprecationLogsFilepath = null)
    {
        $this->warmers = $warmers;
        $this->debug = $debug;
        $this->deprecationLogsFilepath = $deprecationLogsFilepath;
    }

    public function enableOptionalWarmers(): void
    {
        $this->optionalsEnabled = true;
    }

    public function enableOnlyOptionalWarmers(): void
    {
        $this->onlyOptionalsEnabled = $this->optionalsEnabled = true;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir): void
    {
        if ($this->debug) {
            $collectedLogs = [];

            $previousHandler = \defined('PHPUNIT_COMPOSER_INSTALL');
            $previousHandler = $previousHandler ?: \set_error_handler(static function ($type, $message, $file, $line) use (&$collectedLogs, &$previousHandler) {
                if (\E_USER_DEPRECATED !== $type && \E_DEPRECATED !== $type) {
                    return $previousHandler ? $previousHandler($type, $message, $file, $line) : false;
                }

                if (isset($collectedLogs[$message])) {
                    $collectedLogs[$message]['count']++;

                    return;
                }

                $backtrace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 3);

                // Clean the trace by removing first frames added by the error handler itself.
                for ($i = 0; isset($backtrace[$i]); $i++) {
                    if (isset($backtrace[$i]['file'], $backtrace[$i]['line']) && $backtrace[$i]['line'] === $line && $backtrace[$i]['file'] === $file) {
                        $backtrace = \array_slice($backtrace, 1 + $i);

                        break;
                    }
                }

                $collectedLogs[$message] = [
                    'type' => $type,
                    'message' => $message,
                    'file' => $file,
                    'line' => $line,
                    'trace' => $backtrace,
                    'count' => 1,
                ];
            });
        }

        try {
            foreach ($this->warmers as $warmer) {
                if (! $this->optionalsEnabled && $warmer->isOptional()) {
                    continue;
                }

                if ($this->onlyOptionalsEnabled && ! $warmer->isOptional()) {
                    continue;
                }
                $warmer->warmUp($cacheDir);
            }
        } finally {
            if ($this->debug && $previousHandler !== true) {
                \restore_error_handler();

                if (file_exists($this->deprecationLogsFilepath)) {
                    $previousLogs = \unserialize(\file_get_contents($this->deprecationLogsFilepath));
                    $collectedLogs = \array_merge($previousLogs, $collectedLogs);
                }

                \file_put_contents($this->deprecationLogsFilepath, \serialize(\array_values($collectedLogs)));
            }
        }
    }

    public function isOptional(): bool
    {
        return false;
    }
}
