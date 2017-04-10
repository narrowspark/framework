<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\StaticalProxy\AliasLoader;
use Viserio\Component\StaticalProxy\StaticalProxy;

class RegisterStaticalProxys implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(ApplicationContract $app): void
    {
        StaticalProxy::clearResolvedInstances();

        StaticalProxy::setContainer($app);

        (new AliasLoader($app->get(RepositoryContract::class)->get('viserio.app.aliases', [])))->register();
    }
}
