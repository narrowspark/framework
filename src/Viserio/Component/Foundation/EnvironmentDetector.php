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

namespace Viserio\Component\Foundation;

use Closure;
use Viserio\Contract\Foundation\Environment as EnvironmentContract;

class EnvironmentDetector implements EnvironmentContract
{
    /**
     * Indicates if the application is running in the console.
     */
    protected ?bool $isRunningInConsole = null;

    /**
     * {@inheritdoc}
     */
    public function isRunningInConsole(): bool
    {
        if ($this->isRunningInConsole === null) {
            $this->isRunningInConsole = \getenv('APP_RUNNING_IN_CONSOLE') ?: \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true);
        }

        return $this->isRunningInConsole;
    }

    /**
     * {@inheritdoc}
     */
    public function detect(Closure $callback, ?array $consoleArgs = null)
    {
        if ($consoleArgs !== null) {
            // First we will check if an environment argument was passed via console arguments
            // and if it was that automatically overrides as the environment. Otherwise, we
            // will check the environment as a "web" request like a typical HTTP request.
            return $this->detectConsoleEnvironment($callback, $consoleArgs, static function ($v) {
                return \strpos($v, '--env') === 0 || \strpos($v, '-e') === 0;
            }, 'detectWebEnvironment');
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function detectDebug(Closure $callback, ?array $consoleArgs = null)
    {
        if ($consoleArgs !== null) {
            return $this->detectConsoleEnvironment($callback, $consoleArgs, static function ($v) {
                return \strpos($v, '--no-debug') === 0;
            }, 'detectDebugEnvironment');
        }

        return $this->detectDebugEnvironment($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function canCollectCodeCoverage(): bool
    {
        return $this->hasXdebug();
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return \PHP_VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function hasXdebug(): bool
    {
        return \extension_loaded('xdebug');
    }

    /**
     * Set the application environment from command-line arguments.
     *
     * @return bool|string
     */
    protected function detectConsoleEnvironment(Closure $callback, array $args, Closure $filter, string $method)
    {
        $value = $this->getEnvironmentArgument($args, $filter);

        if ($value !== null) {
            $arr = \array_slice(\explode('=', $value), 1);

            return \reset($arr);
        }

        return $this->{$method}($callback);
    }

    /**
     * Set the application environment for a web request.
     *
     * @return bool|string
     */
    protected function detectWebEnvironment(Closure $callback)
    {
        return $callback();
    }

    /**
     * Set the debug mode for a application.
     *
     * @return bool|string
     */
    protected function detectDebugEnvironment(Closure $callback)
    {
        return $callback();
    }

    /**
     * Get the environment argument from the console.
     *
     * @param Closure $callback
     */
    protected function getEnvironmentArgument(array $args, $callback): ?string
    {
        foreach ($args as $value) {
            if ($callback($value)) {
                return $value;
            }
        }

        return null;
    }
}
