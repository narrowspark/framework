<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

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
        $config    = $kernel->getKernelConfigurations();

        foreach ($config['app']['serviceproviders'] as $provider) {
            $container->register($container->resolve($provider));
        }
    }
}
