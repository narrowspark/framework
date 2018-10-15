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
    public static function getPriority(): int
    {
        return 128;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();

        foreach ($kernel->registerServiceProviders() as $provider) {
            $container->register($container->resolve($provider));
        }
    }
}
