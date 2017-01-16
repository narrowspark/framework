<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Console\Application as ApplicationContract;
use Viserio\Component\Contracts\Foundation\Application;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;

class LoadCommands implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        $app->register(new ConsoleServiceProvider());
        $config = $app->get(RepositoryContract::class);

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

                if (preg_match('/commands$/', $key) && is_array($commands = $app->get($key))) {
                    $app->get(ApplicationContract::class)->addCommands($commands);
                }

                if (preg_match('/command.helper$/', $key)) {
                    $app->get(ApplicationContract::class)->setHelperSet($app->get($key));
                }

                if (preg_match('/(command.helpers|commands.helpers)$/', $key) && is_array($helpers = $app->get($key))) {
                    foreach ($helpers as $helper) {
                        $app->get(ApplicationContract::class)->setHelperSet($helper);
                    }
                }
            }
        }
    }
}
