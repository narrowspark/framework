<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Closure;
use Viserio\Component\Contracts\Foundation\Environment as EnvironmentContract;

class EnvironmentDetector implements EnvironmentContract
{
    /**
     * {@inheritdoc}
     */
    public function detect(Closure $callback, array $consoleArgs = null): string
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
        return PHP_VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function hasXdebug(): bool
    {
        return $this->isPHP() && extension_loaded('xdebug');
    }

    /**
     * {@inheritdoc}
     */
    public function isPHP(): bool
    {
        return ! defined('HHVM_VERSION');
    }

    /**
     * {@inheritdoc}
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg';
    }

    /**
     * Set the application environment from command-line arguments.
     *
     * @param \Closure $callback
     * @param array    $args
     *
     * @return string
     */
    protected function detectConsoleEnvironment(Closure $callback, array $args): string
    {
        // First we will check if an environment argument was passed via console arguments
        // and if it was that automatically overrides as the environment. Otherwise, we
        // will check the environment as a "web" request like a typical HTTP request.
        $value = $this->getEnvironmentArgument($args);

        if ($value !== null) {
            $arr = array_slice(explode('=', $value), 1);

            return reset($arr);
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * Set the application environment for a web request.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    protected function detectWebEnvironment(Closure $callback): string
    {
        return call_user_func($callback);
    }

    /**
     * Get the environment argument from the console.
     *
     * @param array $args
     *
     * @return string|null
     */
    protected function getEnvironmentArgument(array $args): ?string
    {
        $callback = function ($k, $v) {
            return self::startsWith($v, '--env');
        };

        foreach ($args as $key => $value) {
            if ($callback($key, $value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private static function startsWith(string $haystack, string $needle): bool
    {
        if ($needle != '' && mb_substr($haystack, 0, mb_strlen($needle)) === $needle) {
            return true;
        }

        return false;
    }
}
