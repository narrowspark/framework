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

namespace Viserio\Contract\Foundation;

use Closure;
use Psr\Container\ContainerInterface;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\LazyProxy\Dumper as ProxyDumperContract;
use Viserio\Contract\Foundation\Environment as EnvironmentContract;

interface Kernel
{
    /**
     * Get a Environment Detector instance.
     */
    public function getEnvironmentDetector(): EnvironmentContract;

    /**
     * Get the container builder instance.
     *
     * @return \Viserio\Contract\Container\ContainerBuilder&\Viserio\Contract\Container\ServiceProvider\ContainerBuilder
     */
    public function getContainerBuilder(): ContainerBuilderContract;

    /**
     * Set a container builder instance.
     *
     * @param \Viserio\Contract\Container\ContainerBuilder&\Viserio\Contract\Container\ServiceProvider\ContainerBuilder $containerBuilder
     *
     * @return static
     */
    public function setContainerBuilder(ContainerBuilderContract $containerBuilder);

    /**
     * Returns a object with \Viserio\Contract\Container\LazyProxy\Dumper interface implemented.
     */
    public function getProxyDumper(): ?ProxyDumperContract;

    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     */
    public function getContainerBaseClass(): string;

    /**
     * Set a container instance.
     *
     * @param \Viserio\Contract\Container\CompiledContainer&\Psr\Container\ContainerInterface $container
     *
     * @return static
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Get the container instance.
     *
     * @return \Viserio\Contract\Container\CompiledContainer&\Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * Bootstrap the kernel.
     */
    public function bootstrap(): void;

    /**
     * Determine if application is in local environment.
     */
    public function isLocal(): bool;

    /**
     * Determine if we are running unit tests.
     */
    public function isRunningUnitTests(): bool;

    /**
     * Determine if we are running in the console.
     */
    public function isRunningInConsole(): bool;

    /**
     * Determine if the application is currently down for maintenance.
     */
    public function isDownForMaintenance(): bool;

    /**
     * Gets the application root dir (path of the project's composer file).
     *
     * @return string The project root dir
     */
    public function getRootDir(): string;

    /**
     * Get the path to the application "app" directory.
     *
     * @param string $path Optionally, a path to append to the app path
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
     */
    public function getPublicPath(string $path = ''): string;

    /**
     * Get the path to the storage directory.
     *
     * The storage path is used by Narrowspark to store cached views, logs
     * and other pieces of information.
     *
     * @param string $path Optionally, a path to append to the storage path
     */
    public function getStoragePath(string $path = ''): string;

    /**
     * Get the path to the resources directory.
     *
     * @param string $path Optionally, a path to append to the resources path
     */
    public function getResourcePath(string $path = ''): string;

    /**
     * Get the path to the language files.
     *
     * This path is used by the language file loader to load your application
     * language files. The purpose of these files is to store your strings
     * that are translated into other languages for views, e-mails, etc.
     */
    public function getLangPath(): string;

    /**
     * Get the path to the routes files.
     *
     * This path is used by the routes loader to load the application
     * routes files. In general, you should'nt need to change this
     * value; however, you can theoretically change the path from here.
     */
    public function getRoutesPath(): string;

    /**
     * Get the path to the tests directory.
     *
     * @param string $path Optionally, a path to append to the tests path
     */
    public function getTestsPath(string $path = ''): string;

    /**
     * Set the directory for the environment file.
     */
    public function useEnvironmentPath(string $path): self;

    /**
     * Set the environment file to be loaded during bootstrapping.
     */
    public function loadEnvironmentFrom(string $file): self;

    /**
     * Get the path to the environment file directory.
     */
    public function getEnvironmentPath(): string;

    /**
     * Get the environment file the application is using.
     */
    public function getEnvironmentFile(): string;

    /**
     * Get the fully qualified path to the environment file.
     */
    public function getEnvironmentFilePath(): string;

    /**
     * Detect the application's current environment.
     */
    public function detectEnvironment(Closure $callback): string;

    /**
     * Gets the environment.
     *
     * @return string The current environment
     */
    public function getEnvironment(): string;

    /**
     * Checks if debug mode is enabled.
     *
     * @return bool true if debug mode is enabled, false otherwise
     */
    public function isDebug(): bool;

    /**
     * Detects if the current application is in debug mode.
     */
    public function detectDebugMode(Closure $callback): bool;

    /**
     * Returns a list of all service providers that will be registered.
     */
    public function getRegisteredServiceProviders(): array;
}
