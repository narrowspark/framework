<?php
declare(strict_types=1);
namespace Viserio\Foundation;

use Closure;
use Narrowspark\Arr\Arr;
use Viserio\Contracts\Foundation\Environment as EnvironmentContract;
use Viserio\Support\Str;

class EnvironmentDetector implements EnvironmentContract
{
    /**
     * {@inheritdoc}
     */
    public function detect(Closure $callback, array $consoleArgs = null): string
    {
        if ($consoleArgs) {
            return $this->detectConsoleEnvironment($callback, $consoleArgs);
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function canCollectCodeCoverage(): bool
    {
        return $this->isHHVM() || $this->hasXdebug();
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        if ($this->isHHVM()) {
            return HHVM_VERSION;
        }

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
    public function isHHVM(): bool
    {
        return defined('HHVM_VERSION');
    }

    /**
     * {@inheritdoc}
     */
    public function isPHP(): bool
    {
        return ! $this->isHHVM();
    }

    /**
     * {@inheritdoc}
     */
    public function runningInConsole(): bool
    {
        return substr(PHP_SAPI, 0, 3) === 'cgi';
    }

    /**
     * {@inheritdoc}
     */
    protected function detectWebEnvironment(Closure $callback)
    {
        return call_user_func($callback);
    }

    /**
     * {@inheritdoc}
     */
    protected function detectConsoleEnvironment(Closure $callback, array $args)
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
