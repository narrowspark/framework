<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Foundation;

use Closure;
use Viserio\Component\Contracts\Container\Container as ContainerContract;

interface Kernel
{
    /**
     * Run the given array of bootstrap classes.
     *
     * @param array $bootstrappers
     *
     * @return void
     */
    public function bootstrapWith(array $bootstrappers): void;

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped(): bool;

    /**
     * Get the container instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contracts\Container\Container
     */
    public function getContainer(): ContainerContract;

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale(): string;

    /**
     * Set the current application locale.
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale(string $locale): Kernel;

    /**
     * Get the application fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale(): string;

    /**
     * Get the path to the environment file directory.
     *
     * @return string
     */
    public function getEnvironmentPath(): string;

    /**
     * Set the directory for the environment file.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useEnvironmentPath(string $path): Kernel;

    /**
     * Set the environment file to be loaded during bootstrapping.
     *
     * @param string $file
     *
     * @return $this
     */
    public function loadEnvironmentFrom(string $file): Kernel;

    /**
     * Get the environment file the application is using.
     *
     * @return string
     */
    public function getEnvironmentFile(): string;

    /**
     * Detect the application's current environment.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    public function detectEnvironment(Closure $callback): string;

    /**
     * Determine if we are running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests(): bool;
}
