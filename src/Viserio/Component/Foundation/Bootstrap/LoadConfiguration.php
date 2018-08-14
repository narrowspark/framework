<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class LoadConfiguration extends AbstractLoadFiles implements BootstrapContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * Supported config files.
     */
    private const CONFIG_EXTS = [
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
    ];

    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $loadedFromCache = false;
        $container       = $kernel->getContainer();

        $container->register(new ConfigServiceProvider());

        $config = $container->get(RepositoryContract::class);

        // First we will see if we have a cache configuration file.
        // If we do, we'll load the configuration items.
        if (\file_exists($cached = $kernel->getStoragePath('framework/config.cache.php'))) {
            $items = require self::normalizeDirectorySeparator($cached);

            $config->setArray($items, true);

            $loadedFromCache = true;
        }

        // Next we will spin through all of the configuration files in the configuration
        // directory and load each one into the config manager.
        if (! $loadedFromCache) {
            $this->loadConfigurationFiles($kernel, $config);
        }

        $kernel->detectEnvironment(function () use ($config) {
            return $config->get('viserio.env', 'prod');
        });

        \date_default_timezone_set($config->get('viserio.app.timezone', 'UTC'));

        \mb_internal_encoding('UTF-8');
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     * @param \Viserio\Component\Contract\Config\Repository $config
     *
     * @return void
     */
    protected function loadConfigurationFiles(KernelContract $kernel, RepositoryContract $config): void
    {
        foreach ($this->getFiles($kernel->getConfigPath(), self::CONFIG_EXTS) as $path) {
            $config->import(self::normalizeDirectorySeparator($path));
        }

        foreach ($this->getFiles($kernel->getConfigPath($kernel->getEnvironment()), self::CONFIG_EXTS) as $path) {
            $config->import(self::normalizeDirectorySeparator($path));
        }
    }
}
