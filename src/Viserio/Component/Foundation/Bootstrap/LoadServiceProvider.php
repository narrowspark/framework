<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Application;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;

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
