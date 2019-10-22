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

namespace Viserio\Contract\Foundation;

use Closure;
use Psr\Container\ContainerInterface;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\LazyProxy\Dumper as ProxyDumperContract;
use Viserio\Contract\Foundation\Environment as EnvironmentContract;

interface Kernel
{
    /**
     * Gets the charset of the application.
     *
     * @return string The charset
     */
    public function getCharset(): string;

    /**
     * Get a Environment Detector instance.
     *
     * @return \Viserio\Contract\Foundation\Environment
     */
    public function getEnvironmentDetector(): EnvironmentContract;

    /**
     * Get the container builder instance.
     *
     * @return \Viserio\Contract\Container\ContainerBuilder & \Viserio\Contract\Container\ServiceProvider\ContainerBuilder
     */
    public function getContainerBuilder(): ContainerBuilderContract;

    /**
     * Set a container builder instance.
     *
     * @param \Viserio\Contract\Container\ContainerBuilder & \Viserio\Contract\Container\ServiceProvider\ContainerBuilder $containerBuilder
     *
     * @return static
     */
    public function setContainerBuilder(ContainerBuilderContract $containerBuilder);

    /**
     * Returns a object with \Viserio\Contract\Container\LazyProxy\Dumper interface implemented.
     *
     * @return null|\Viserio\Contract\Container\LazyProxy\Dumper
     */
    public function getProxyDumper(): ?ProxyDumperContract;

    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     *
     * @return string
     */
    public function getContainerBaseClass(): string;

    /**
     * Set a container instance.
     *
     * @param \Viserio\Contract\Container\CompiledContainer & \Psr\Container\ContainerInterface $container
     *
     * @return static
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Get the container instance.
     *
     * @return \Viserio\Contract\Container\CompiledContainer & \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * Set the kernel configuration.
     *
     * @param array|\ArrayAccess $config
     *
     * @return void
     */
    public function setKernelConfigurations($config): void;

    /**
     * Get the kernel configuration.
     *
     * @return array
     */
    public function getKernelConfigurations(): array;

    /**
     * Bootstrap the kernel.
     *
     * @return void
     */
    public function bootstrap(): void;

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
    public function getRootDir(): string;

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
     * Get the path to the tests directory.
     *
     * @param string $path Optionally, a path to append to the tests path
     *
     * @return string
     */
    public function getTestsPath(string $path = ''): string;

    /**
     * Set the directory for the environment file.
     *
     * @param string $path
     *
     * @return self
     */
    public function useEnvironmentPath(string $path): self;

    /**
     * Set the environment file to be loaded during bootstrapping.
     *
     * @param string $file
     *
     * @return self
     */
    public function loadEnvironmentFrom(string $file): self;

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
     *
     * @param Closure $callback
     *
     * @return bool
     */
    public function detectDebugMode(Closure $callback): bool;

    /**
     * Register all of the application / kernel service providers.
     *
     * @return array
     */
    public function registerServiceProviders(): array;
}
