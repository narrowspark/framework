<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\StaticalProxy\AliasLoader;
use Viserio\Component\StaticalProxy\StaticalProxy;

class RegisterStaticalProxys implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();

        StaticalProxy::clearResolvedInstances();

        StaticalProxy::setContainer($container);

        (new AliasLoader($container->get(RepositoryContract::class)->get('viserio.app.aliases', [])))->register();
    }
}
