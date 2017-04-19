<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class ConfigureKernel implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();
        $resolver  = $container->get(OptionsResolver::class);

        $kernel->setConfigurations($resolver->configure($kernel, $container)->resolve());
    }
}
