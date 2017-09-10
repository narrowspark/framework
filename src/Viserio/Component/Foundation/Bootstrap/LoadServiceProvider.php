<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;

class LoadServiceProvider implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();

        foreach ($this->registerServiceProviders($kernel) as $provider) {
            $container->register($container->resolve($provider));
        }
    }

    /**
     * Register all of the application / kernel service providers.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     *
     * @return array
     */
    protected function registerServiceProviders(KernelContract $kernel): array
    {
        $providers = $kernel->getConfigPath('/serviceproviders.php');

        if (\file_exists($providers)) {
            return require_once $providers;
        }

        return [];
    }
}
