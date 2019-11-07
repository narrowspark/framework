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

namespace Viserio\Component\Foundation;

use Closure;
use Viserio\Contract\Foundation\Environment as EnvironmentContract;

class EnvironmentDetector implements EnvironmentContract
{
    /**
     * {@inheritdoc}
     */
    public function detect(Closure $callback, ?array $consoleArgs = null)
    {
        if ($consoleArgs !== null) {
            return $this->detectConsoleEnvironment($callback, $consoleArgs);
        }

        return $this->detectWebEnvironment($callback);
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
        return $this->isPHP() && \extension_loaded('xdebug');
    }

    /**
     * {@inheritdoc}
     */
    public function isPHP(): bool
    {
        return ! \defined('HHVM_VERSION');
    }

    /**
     * {@inheritdoc}
     */
    public function runningInConsole(): bool
    {
        return \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true);
    }

    /**
     * Set the application environment from command-line arguments.
     *
     * @param Closure $callback
     * @param array   $args
     *
     * @return bool|string
     */
    protected function detectConsoleEnvironment(Closure $callback, array $args)
    {
        // First we will check if an environment argument was passed via console arguments
        // and if it was that automatically overrides as the environment. Otherwise, we
        // will check the environment as a "web" request like a typical HTTP request.
        $value = $this->getEnvironmentArgument($args);

        if ($value !== null) {
            $arr = \array_slice(\explode('=', $value), 1);

            return \reset($arr);
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * Set the application environment for a web request.
     *
     * @param Closure $callback
     *
     * @return bool|string
     */
    protected function detectWebEnvironment(Closure $callback)
    {
        return $callback();
    }

    /**
     * Get the environment argument from the console.
     *
     * @param array $args
     *
     * @return null|string
     */
    protected function getEnvironmentArgument(array $args): ?string
    {
        $callback = static function ($v) {
            return \strpos($v, '--env') === 0;
        };

        foreach ($args as $key => $value) {
            if ($callback($value)) {
                return $value;
            }
        }

        return null;
    }
}
