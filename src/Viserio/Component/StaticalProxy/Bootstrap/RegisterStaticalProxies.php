<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Bootstrap;

use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\StaticalProxy\AliasLoader as AliasLoaderContract;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\StaticalProxy\StaticalProxy;

class RegisterStaticalProxies implements BootstrapStateContract
{
    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 32;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return BootstrapStateContract::TYPE_AFTER;
    }

    /**
     * {@inheritdoc}
     */
    public static function getBootstrapper(): string
    {
        return LoadServiceProvider::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();

        StaticalProxy::clearResolvedInstances();
        StaticalProxy::setContainer($container);

        $container->get(AliasLoaderContract::class)->register();
    }
}
