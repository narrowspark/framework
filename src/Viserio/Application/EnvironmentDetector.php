<?php
declare(strict_types=1);
namespace Viserio\Application;

use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Application\Environment as EnvironmentContract;
use Viserio\Support\Str;

class EnvironmentDetector implements EnvironmentContract
{
    /**
     * Detect the application's current environment.
     *
     * @param \Closure   $callback
     * @param array|null $consoleArgs
     *
     * @return string
     */
    public function detect(\Closure $callback, array $consoleArgs = null): string
    {
        if ($consoleArgs) {
            return $this->detectConsoleEnvironment($callback, $consoleArgs);
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * Returns true when the runtime used is HHVM or
     * the runtime used is PHP + Xdebug.
     *
     * @return bool
     */
    public function canCollectCodeCoverage(): bool
    {
        return $this->isHHVM() || $this->hasXdebug();
    }

    /**
     * Returns the running php/HHVM version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        if ($this->isHHVM()) {
            return HHVM_VERSION;
        }

        return PHP_VERSION;
    }

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     *
     * @return bool
     */
    public function hasXdebug(): bool
    {
        return $this->isPHP() && extension_loaded('xdebug');
    }

    /**
     * Returns true when the runtime used is HHVM.
     *
     * @return bool
     */
    public function isHHVM(): bool
    {
        return defined('HHVM_VERSION');
    }

    /**
     * Returns true when the runtime used is PHP.
     *
     * @return bool
     */
    public function isPHP(): bool
    {
        return ! $this->isHHVM();
    }

    /**
     * Returns true when the runtime used is Console.
     *
     * @return bool
     */
    public function runningInConsole(): bool
    {
        return substr(PHP_SAPI, 0, 3) === 'cgi';
    }

    /**
     * Set the application environment for a web request.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    protected function detectWebEnvironment(\Closure $callback)
    {
        return call_user_func($callback);
    }

    /**
     * Set the application environment from command-line arguments.
     *
     * @param \Closure $callback
     * @param array    $args
     *
     * @return string
     */
    protected function detectConsoleEnvironment(\Closure $callback, array $args)
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
     * Get the environment argument from the console.
     *
     * @param array $args
     *
     * @return string|null
     */
    protected function getEnvironmentArgument(array $args)
    {
        return Arr::first($args, function ($k, $v) {
            return Str::startsWith($v, '--env');
        });
    }
}
