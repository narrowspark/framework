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
    public function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();
        $providers = $container->get(RepositoryContract::class)->get('viserio.app.serviceproviders', []);

        if (count($providers) > 0) {
            foreach ($providers as $provider) {
                $container->register($container->make($provider));
            }
        }
    }
}
