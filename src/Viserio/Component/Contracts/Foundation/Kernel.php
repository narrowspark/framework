<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Foundation;

use Closure;
use Viserio\Component\Contracts\Container\Container as ContainerContract;

interface Kernel
{
    /**
     * Get the container instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contracts\Container\Container
     */
    public function getContainer(): ContainerContract;

    /**
     * Set the kernel configuration.
     *
     * @param \Interop\Container\ContainerInterface|iterable $data
     *
     * @return void
     */
    public function setConfigurations($data): void;

    /**
     * Get the kernel configuration.
     *
     * @return array
     */
    public function getConfigurations(): array;

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
    public function isRunningUnitTests(): bool;

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function isRunningInConsole(): bool;

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance(): bool;

    /**
     * Gets the application root dir (path of the project's composer file).
     *
     * @return string The project root dir
     */
    public function getProjectDir(): string;

    /**
     * Get the path to the application "app" directory.
     *
     * @param string $path Optionally, a path to append to the app path
     *
     * @return string
     */
    public function getAppPath(string $path = ''): string;

    /**
     * Get the path to the application configuration files.
     *
     * This path is used by the configuration loader to load the application
     * configuration files. In general, you should'nt need to change this
     * value; however, you can theoretically change the path from here.
     *
     * @param string $path Optionally, a path to append to the config path
     *
     * @return string
     */
    public function getConfigPath(string $path = ''): string;

    /**
     * Get the path to the database directory.
     *
     * This path is used by the migration generator and migration runner to
     * know where to place your fresh database migration classes. You're
     * free to modify the path but you probably will not ever need to.
     *
     * @param string $path Optionally, a path to append to the database path
     *
     * @return string
     */
    public function getDatabasePath(string $path = ''): string;

    /**
     * Get the path to the public / web directory.
     *
     * The public path contains the assets for your web application, such as
     * your JavaScript and CSS files, and also contains the primary entry
     * point for web requests into these applications from the outside.
     *
     * @param string $path Optionally, a path to append to the public path
     *
     * @return string
     */
    public function getPublicPath(string $path = ''): string;

    /**
     * Get the path to the storage directory.
     *
     * The storage path is used by Narrowspark to store cached views, logs
     * and other pieces of information.
     *
     * @param string $path Optionally, a path to append to the storage path
     *
     * @return string
     */
    public function getStoragePath(string $path = ''): string;

    /**
     * Get the path to the resources directory.
     *
     * @param string $path Optionally, a path to append to the resources path
     *
     * @return string
     */
    public function getResourcePath(string $path = ''): string;

    /**
     * Get the path to the language files.
     *
     * This path is used by the language file loader to load your application
     * language files. The purpose of these files is to store your strings
     * that are translated into other languages for views, e-mails, etc.
     *
     * @return string
     */
    public function getLangPath(): string;

    /**
     * Get the path to the routes files.
     *
     * This path is used by the routes loader to load the application
     * routes files. In general, you should'nt need to change this
     * value; however, you can theoretically change the path from here.
     *
     * @return string
     */
    public function getRoutesPath(): string;

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
     * Get the path to the environment file directory.
     *
     * @return string
     */
    public function getEnvironmentPath(): string;

    /**
     * Get the environment file the application is using.
     *
     * @return string
     */
    public function getEnvironmentFile(): string;

    /**
     * Get the fully qualified path to the environment file.
     *
     * @return string
     */
    public function getEnvironmentFilePath(): string;

    /**
     * Detect the application's current environment.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    public function detectEnvironment(Closure $callback): string;
}
