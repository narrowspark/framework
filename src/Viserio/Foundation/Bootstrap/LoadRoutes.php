<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;

class LoadRoutes extends AbstractLoadFiles implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        $routesPath = realpath($app->get(ConfigManager::class)->get('path.routes'));

        foreach ($this->getFiles($routesPath) as $key => $path) {
            require_once $path;
        }
    }
}
