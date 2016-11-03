<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\StaticalProxy\AliasLoader;
use Viserio\StaticalProxy\StaticalProxy;

class RegisterStaticalProxys implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        StaticalProxy::clearResolvedInstances();

        StaticalProxy::setContainer($app);

        (new AliasLoader($app->get(ConfigManager::class)->get('app.aliases', [])))->register();
    }
}
