<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Viserio\Config\Manager as ConfigManager;
use Viserio\Console\Providers\ConsoleServiceProvider;
use Viserio\Contracts\Console\Application as ApplicationContract;
use Viserio\Contracts\Container\Types as TypesContract;
use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;

class LoadCommands implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        $app->register(new ConsoleServiceProvider());
        $config = $app->get(ConfigManager::class);

        $loadedFromCache = false;

        // First we will see if we have a cache console commands file.
        // If we do, we'll load the console commands.
        if (file_exists($cached = $config->get('patch.cached.commands'))) {
            foreach ($cached as $command) {
                $app->get(ApplicationContract::class)->add($command);
            }

            $loadedFromCache = true;
        }

        if (! $loadedFromCache) {
            foreach ($app->getBindings() as $key => $binding) {
                if (preg_match('/command$/', $key)) {
                    $app->get(ApplicationContract::class)->add($app->get($key));
                }
            }
        }
    }
}
