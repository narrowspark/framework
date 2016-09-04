<?php
declare(strict_types=1);
namespace Viserio\Foundation\Traits;

trait PathsTrait
{
    /**
     * Bind the installation paths to the config.
     *
     * @param array $paths
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function bindInstallPaths(array $paths)
    {
        // Each path key is prefixed with path
        // so that they have the consistent naming convention.
        foreach ($paths as $key => $value) {
            $this->instance(sprintf('path.%s', $key), realpath($value));
        }

        return $this;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path(): string
    {
        return $this->get('path.app');
    }

    /**
     * Get the path to the application configuration files.
     *
     * @return string
     */
    public function configPath(): string
    {
        return $this->get('path.config');
    }

    /**
     * Get the path to the application routes files.
     *
     * @return string
     */
    public function routesPath(): string
    {
        return $this->get('path.route');
    }

    /**
     * Get the path to the database directory.
     *
     * @return string
     */
    public function databasePath(): string
    {
        return $this->get('path.database');
    }

    /**
     * Get the path to the language files.
     *
     * @return string
     */
    public function langPath(): string
    {
        return $this->get('path.lang');
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath(): string
    {
        return $this->get('path.public');
    }

    /**
     * Get the path to the base ../ directory.
     *
     * @return string
     */
    public function basePath(): string
    {
        return $this->get('path.base');
    }

    /**
     * Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath(): string
    {
        return $this->get('path.storage');
    }

    /**
     * Get the path to the configuration cache file.
     *
     * @return string
     */
    public function getCachedConfigPath():string
    {
        return $this->storagePath() . '/framework/cache/config.php';
    }
}
