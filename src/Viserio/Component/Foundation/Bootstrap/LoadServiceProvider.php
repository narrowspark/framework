<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;

class LoadServiceProvider implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(ApplicationContract $app): void
    {
        $providers = $app->get(RepositoryContract::class)->get('app.serviceproviders', []);

        if (count($providers) > 0) {
            foreach ($providers as $provider) {
                $app->register($app->make($provider));
            }
        }
    }
}
