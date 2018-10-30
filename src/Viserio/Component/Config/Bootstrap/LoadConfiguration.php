<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Bootstrap;

use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\AbstractLoadFiles;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;

class LoadConfiguration extends AbstractLoadFiles implements BootstrapStateContract
{
    /**
     * Supported config files.
     *
     * @var string[]
     */
    protected const CONFIG_EXTS = [
        'php',
        'xml',
        'yaml',
        'yml',
        'toml',
    ];

    /**
     * {@inheritdoc}
     */
    protected static $bypassFiles = [
        'serviceproviders',
        'staticalproxy',
        'bootstrap',
    ];

    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 32;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return BootstrapStateContract::TYPE_BEFORE;
    }

    /**
     * {@inheritdoc}
     */
    public static function getBootstrapper(): string
    {
        return LoadServiceProvider::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();
        $container->register(new ConfigServiceProvider());

        $config = $container->get(RepositoryContract::class);

        $loadedFromCache = false;

        // First we will see if we have a cache configuration file.
        // If we do, we'll load the configuration items.
        if (\file_exists($cached = $kernel->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'config.cache.php'))) {
            $items = require $cached;

            $config->setArray($items, true);

            $loadedFromCache = true;
        }

        // Next we will spin through all of the configuration files in the configuration
        // directory and load each one into the config manager.
        if (! $loadedFromCache) {
            $config->set('viserio.app.env', $kernel->getEnvironment());

            static::loadConfigurationFiles($kernel, $config);
        }
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     * @param \Viserio\Component\Contract\Config\Repository $config
     *
     * @return void
     */
    protected static function loadConfigurationFiles(KernelContract $kernel, RepositoryContract $config): void
    {
        foreach (static::getFiles($kernel->getConfigPath('packages'), self::CONFIG_EXTS) as $path) {
            $config->import($path);
        }

        foreach (static::getFiles($kernel->getConfigPath(), self::CONFIG_EXTS) as $path) {
            $config->import($path);
        }

        foreach (static::getFiles($kernel->getConfigPath($kernel->getEnvironment()), self::CONFIG_EXTS) as $path) {
            $config->import($path);
        }

        foreach (static::getFiles($kernel->getConfigPath('packages' . \DIRECTORY_SEPARATOR . $kernel->getEnvironment()), self::CONFIG_EXTS) as $path) {
            $config->import($path);
        }
    }
}
