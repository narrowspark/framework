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
        if (\file_exists($cached = $kernel->getStoragePath('config.cache'))) {
            $items = require self::normalizeDirectorySeparator($cached);

            $config->setArray($items);

            $loadedFromCache = true;
        }

        // Next we will spin through all of the configuration files in the configuration
        // directory and load each one into the config manager.
        if (! $loadedFromCache) {
            $this->loadConfigurationFiles($kernel, $config);
        }

        $kernel->detectEnvironment(function () use ($config) {
            return $config->get('viserio.app.env', 'prod');
        });

        \date_default_timezone_set($config->get('viserio.app.timezone', 'UTC'));

        \mb_internal_encoding('UTF-8');
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     * @param \Viserio\Component\Contract\Config\Repository $config
     */
    protected function loadConfigurationFiles(KernelContract $kernel, RepositoryContract $config): void
    {
        foreach ($this->getFiles($kernel->getConfigPath()) as $key => $path) {
            if ($key === 'serviceproviders') {
                continue;
            }

            $config->import(self::normalizeDirectorySeparator($path));
        }
    }
}
