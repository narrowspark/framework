<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Foundation;

use Closure;
use Viserio\Component\Contracts\Container\Container;

interface Application extends Container
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
    public function setLocale(string $locale): Application;

    /**
     * Determine if application locale is the given locale.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function isLocale(string $locale): bool;

    /**
     * Get the application fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale(): string;

    /**
     * Determine if the application supports the given locale.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function hasLocale(string $locale): bool;

    /**
     * Get the path to the environment file directory.
     *
     * @return string
     */
    public function environmentPath(): string;

    /**
     * Set the directory for the environment file.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useEnvironmentPath(string $path): Application;

    /**
     * Set the environment file to be loaded during bootstrapping.
     *
     * @param string $file
     *
     * @return $this
     */
    public function loadEnvironmentFrom(string $file): Application;

    /**
     * Get the environment file the application is using.
     *
     * @return string
     */
    public function environmentFile(): string;

    /**
     * Get the fully qualified path to the environment file.
     *
     * @return string
     */
    public function environmentFilePath(): string;

    /**
     * Detect the application's current environment.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    public function detectEnvironment(Closure $callback): string;

    /**
     * Determine if application is in local environment.
     *
     * @return bool
     */
    public function isLocal(): bool;

    /**
     * Determine if we are running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests(): bool;
}
