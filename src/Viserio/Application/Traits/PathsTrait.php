<?php
namespace Viserio\Application\Traits;

use Narrowspark\Arr\StaticArr as Arr;

trait PathsTrait
{
    /**
     * Bind the installation paths to the application.
     *
     * @param array $paths
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function bindInstallPaths(array $paths)
    {
        $this->bind('path', realpath($paths['app']));

        // Each path key is prefixed with path
        // so that they have the consistent naming convention.
        foreach (Arr::except($paths, ['app']) as $key => $value) {
            $this->bind(sprintf('path.%s', $key), realpath($value));
        }

        return $this;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return $this->get('path');
    }

    /**
     * Get the path to the application configuration files.
     *
     * @return string
     */
    public function configPath()
    {
        return $this->get('path.config');
    }

    /**
     * Get the path to the database directory.
     *
     * @return string
     */
    public function databasePath()
    {
        return $this->get('path.database');
    }

    /**
     * Get the path to the language files.
     *
     * @return string
     */
    public function langPath()
    {
        return $this->get('path.lang');
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath()
    {
        return $this->get('path.public');
    }

    /**
     * Get the path to the base ../ directory.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->get('path.base');
    }

    /**
     * Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->get('path.storage');
    }

    /**
     * {@inheritdoc}
     *
     * @param string $concrete
     * @param string $alias
     */
    abstract public function bind(string $alias, $concrete = null, $singleton = false);

    /**
     * {@inheritdoc}
     *
     * @param string $id
     */
    abstract public function get($id);
}
