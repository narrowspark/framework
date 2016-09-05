<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;

class LoadServiceProvider implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        $config = $app->get(ConfigManager::class);
        $providers = $config->get('app.serviceprovider', []);

        if (count($providers) > 0) {
            foreach ($providers as $provider) {
                $app->register($this->make($provider));
            }
        }
    }
}
