<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;

class LoadConfiguration extends AbstractLoadFiles implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        $loadedFromCache = false;
        $config = $app->get(RepositoryContract::class);

        // First we will see if we have a cache configuration file.
        // If we do, we'll load the configuration items.
        if (file_exists($cached = $config->get('patch.cached.config'))) {
            $config->setArray($cached);

            $loadedFromCache = true;
        }

        // Next we will spin through all of the configuration files in the configuration
        // directory and load each one into the config manager.
        if (! $loadedFromCache) {
            $this->loadConfigurationFiles($app, $config);
        }

        $app->detectEnvironment(function () use ($config) {
            return $config->get('app.env', 'production');
        });

        date_default_timezone_set($config->get('app.timezone'));

        mb_internal_encoding('UTF-8');
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     * @param \Viserio\Contracts\Config\Repository      $configManager
     */
    protected function loadConfigurationFiles(Application $app, RepositoryContract $configManager)
    {
        $configPath = realpath($app->get(RepositoryContract::class)->get('path.config'));

        foreach ($this->getFiles($configPath) as $key => $path) {
            $configManager->import($path);
        }
    }
}
