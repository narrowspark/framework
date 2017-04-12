<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;

class LoadServiceProvider implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $app): void
    {
        $providers = $app->get(RepositoryContract::class)->get('viserio.app.serviceproviders', []);

        if (count($providers) > 0) {
            foreach ($providers as $provider) {
                $app->register($app->make($provider));
            }
        }
    }
}
