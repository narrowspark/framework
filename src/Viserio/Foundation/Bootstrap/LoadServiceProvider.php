<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;

class LoadServiceProvider implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        $config    = $app->get(RepositoryContract::class);
        $providers = $config->get('app.serviceproviders', []);

        if (count($providers) > 0) {
            foreach ($providers as $provider) {
                $app->register($app->make($provider));
            }
        }
    }
}
